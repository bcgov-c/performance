<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GenericTemplate;
use App\Models\GenericTemplateBind;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GenericTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $search = $request->q;

        if (isset($search)) {
            //dd($request->search);
            $generic_templates = GenericTemplate::where('template', 'like', '%'. $search .'%')
                ->orWhere('description', 'like', '%'. $search .'%')
                ->orderBy('template')->paginate(10);

            $generic_templates->appends(['q' => $request->q ]);

        } else {
            // get all the record 
            $generic_templates = GenericTemplate::orderBy('template')->paginate(10);
        }   

        // load the view and pass the sharks
        return view('sysadmin.notifications.generic-template.index', compact('generic_templates','search'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('sysadmin.notifications.generic-template.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // array for build the select option on the page
        if ($request->sender_id) {
            $sender_ids = User::where('id', $request->sender_id)->pluck('name','id');
            $request->session()->flash('old_sender_ids', 
                //[$request->email => $this->getAzureEmail($request->email) ]
                $sender_ids
            );
        }

        //setup Validator and passing request data and rules
        $validator = Validator::make(request()->all(), [
            'template'           => 'required|min:10|max:30|unique:generic_templates,template',
            'description'        => 'required|max:250',
            'instructional_text' => 'required',
            'sender'             => 'required',
            'sender_id'          => '',
            'subject'            => 'required',
            'body'               => 'required',
        ]);


        //hook to add additional rules by calling the ->after method
        $validator->after(function ($validator) {
            
            if ( request('sender') == 1 && (!(empty(request('sender_id')))) ) {
                $validator->errors()->add('sender_id', 'The user name field is not required for sender type is User'); 
            }

            if ( request('sender') == 2 && (!(empty(request('sender_id')))) ) {
                $validator->errors()->add('sender_id', 'The user name field is not required for sender type is System'); 
            }

            // if ( request('sender') == 2 && (empty(request('sender_id'))) ) {
            //     $validator->errors()->add('sender_id', 'The user name field is required.'); 
            // }

            if ( str_contains( request('template') ?? '', ' ')) {
                $validator->errors()->add('template', 'The template name contains space.');
            }
    
            if (preg_match('/[a-z]/', request('template') ,$matches)) {
                $validator->errors()->add('template', 'The template name must be upper case.');
            }

            //
            $binds = request('binds');
            $descriptions = request('descriptions');
            if ($binds) {
                for ($i=0; $i < count($binds); $i++) {
                    if ($binds[$i] != '' && empty($descriptions[$i]) ) {
                        $validator->errors()->add('description' .$i, 'required.');
                    }
                    if (empty($binds[$i])  && $descriptions[$i] != '' ) {
                        $validator->errors()->add('bind' .$i, 'required.');
                    }
                    if (empty($binds[$i]) && empty($descriptions[$i]) ) {
                        $validator->errors()->add('bind' .$i, 'required.');
                        $validator->errors()->add('description' .$i, 'required.');
                    }
                }
            }   

            // Validate detail
            $collection = collect( request('binds') );
            $collection = $collection->duplicates();
  
             if ($collection) {
                foreach ($collection->all() as $key => $value) {
                    $validator->errors()->add('bind' .$key, 'duplicate bind.');
                }
            }
        });
        
        if (count($validator->errors())>0) {
            $request->session()->flash('message', 'There are one or more errors on the page. Please review and try again.');            
        }
        //run validation which will redirect on failure
        $validator->validate();

        // if ($request->email) {
        //     //$email = $this->getAzureEmail($request->email);   
        //     $email = ($user = User::find($request->email)) ? $user->email : null;
        // }

        $generic_template = GenericTemplate::Create(
            [
            'template' => $request->template,
            'description' =>  $request->description,
            'instructional_text' => $request->instructional_text,
            'sender' => $request->sender,
            'sender_id' => $request->sender_id,
            //'email' => $email ?? '',
            //'azure_id' => $request->email,
            'subject' => $request->subject,
            'body' => $request->body,
            'created_by_id' => Auth::id(),
            ]
        );

    
        $binds = $request->input('binds', []);
        $descriptions = $request->input('descriptions', []);
        if ($binds) {
            for ($i=0; $i < count($binds); $i++) {
                if ($binds[$i] != '') {
                    $generic_template->binds()->create([
                        'seqno' => $i,
                        'bind' => $binds[$i], 
                        'description' => $descriptions[$i],
                    ]);        
                }
            }
        }

        return redirect()->route('generic-template.index')
            ->with('success','Template ' . $request->template  . ' created successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $generic_template = GenericTemplate::find($id);

        // show the view and pass the campaign year to it
        return view('sysadmin.notifications.generic-template.show', compact('generic_template'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
        $generic_template = GenericTemplate::find($id);

        if (!Session()->get('old_sender_ids')) {
            $sender_ids = User::where('id', $generic_template->sender_id)->pluck('name','id');
            Session()->flash('old_sender_ids', 
                $sender_ids
            );
        }


        $generic_template->load('binds');

        // show the view and pass the campaign year to it
        return view('sysadmin.notifications.generic-template.edit', compact('generic_template'));
         
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // if ($request->email) {
        //     $email = ($user = User::find($request->email)) ? $user->email : null;
        //     $request->session()->flash('old_emails', 
        //       //[$request->email => $this->getAzureEmail($request->email) ]
        //       [$request->email => $email ]
        //     );
        // }
        if ($request->sender_id) {
            $sender_ids = User::where('id', $request->sender_id)->pluck('name','id');
            $request->session()->flash('old_sender_ids', 
                $sender_ids
            );
        }

         //setup Validator and passing request data and rules
         $validator = Validator::make(request()->all(), [
            'template'           => 'required|min:10|max:30',
            'description'        => 'required|max:250',
            'instructional_text' => 'required',
            'sender'             => 'required',
            'subject'            => 'required',
            'body'               => 'required',
        ]);

        //hook to add additional rules by calling the ->after method
        $validator->after(function ($validator) {

            if ( request('sender') == 1 && (!(empty(request('sender_id')))) ) {
                $validator->errors()->add('sender_id', 'The user name field is not required for sender type is User'); 
            }

            if ( request('sender') == 2 && (!(empty(request('sender_id')))) ) {
                $validator->errors()->add('sender_id', 'The user name field is not required for sender type is System'); 
            }
            // if ( request('sender') == 2 && (empty(request('sender_id'))) ) {
            //     $validator->errors()->add('sender_id', 'The user name field is required.'); 
            // }

            if ( str_contains( request('template') ?? '', ' ')) {
                $validator->errors()->add('template', 'The template name contains space.');
            }
    
            if (preg_match('/[a-z]/', request('template') ,$matches)) {
                $validator->errors()->add('template', 'The template name must be upper case.');
            }

            //
            $binds = request('binds');
            $descriptions = request('descriptions');
            if ($binds) {
                for ($i=0; $i < count($binds); $i++) {
                    if ($binds[$i] != '' && empty($descriptions[$i]) ) {
                        $validator->errors()->add('description' .$i, 'required.');
                    }
                    if (empty($binds[$i])  && $descriptions[$i] != '' ) {
                        $validator->errors()->add('bind' .$i, 'required.');
                    }
                    if (empty($binds[$i]) && empty($descriptions[$i]) ) {
                        $validator->errors()->add('bind' .$i, 'required.');
                        $validator->errors()->add('description' .$i, 'required.');
                    }
                }
            }

            // Validate detail
            $collection = collect( request('binds') );
            $collection = $collection->duplicates();
  
             if ($collection) {
                foreach ($collection->all() as $key => $value) {
                    $validator->errors()->add('bind' .$key, 'duplicate bind.');
                }
            }
        });
        error_log(print_r($validator->errors(),true));
        //run validation which will redirect on failure
        $validator->validate();

        // if ($request->email) {
        //     //$email = $this->getAzureEmail($request->email);   
        //     $email = ($user = User::find($request->email)) ? $user->email : null;
        // }

        $generic_template = GenericTemplate::find($id);
        $generic_template->update([
            'description' =>  $request->description,
            'instructional_text' => $request->instructional_text,
            'sender' => $request->sender,
            'sender_id' => $request->sender_id,
            //'email' => $email ?? '',
            //'azure_id' => $request->email,
            'subject' => $request->subject,
            'body' => $request->body,
            'modified_by_id' => Auth::id(),
        ]);

        // Update 
        foreach ($generic_template->binds as $bind) {
            $bind->delete();
        }

        $binds = $request->input('binds', []);
        $descriptions = $request->input('descriptions', []);
        if ($binds) {
            for ($i=0; $i < count($binds); $i++) {
                if ($binds[$i] != '') {
                    $generic_template->binds()->create([
                        'seqno' => $i,
                        'bind' => $binds[$i], 
                        'description' => $descriptions[$i],
                    ]);        
                }
            }
        }

        return redirect()->route('generic-template.index')
            ->with('success','Template ' . GenericTemplate::find($id)->template  . ' updated successfully');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getUsers(Request $request)
    {
        /*
        $tokenCache = new TokenCache();
        $accessToken = $tokenCache->getAccessToken();
    
        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $queryParams = array(
           '$select' => 'id,displayName,mail,userPrincipalName',
           //'$filter'  =>  "startswith(displayName,'". $request->q . "')",
           //'$search'  =>  '"displayName:' . $request->q . '"',
           '$orderby' => 'displayName'
         );
     
       if (trim($request->q)) {
           $queryParams['$search'] = '"displayName:' . trim($request->q) . '"';
       }
  
        // test User  API https://graph.microsoft.com/v1.0/users
       $getUsersUrl = '/users?'.http_build_query($queryParams);
       $users = $graph->createRequest('GET', $getUsersUrl)
              ->addHeaders(['ConsistencyLevel'=> 'eventual'])
               ->setReturnType(Model\User::class)
               ->execute();

        $formatted_users = [];
        foreach ($users as $user) {
             $formatted_users[] = [
                    'id' => $user->getId(), 
                    //'text' => $user->getDisplayName() . ' (' . $user->getMail() . ')',
                    'text' => $user->getMail(),
            ];
        }
        */

        $users = User::whereRaw("lower(name) like '%". strtolower($request->q)."%'")
                    ->whereNotNull('azure_id') 
                    ->get(['id','name']);
        //$formatted_users = $users->only(['id', 'name'])->toArray();
        $formatted_users = [];
        foreach ($users as $user) {
             $formatted_users[] = [
                    'id' => $user->id, 
                    'text' => $user->name,
            ];
        }

        // return "[{'id':31,'name':'Abc'}, {'id':32,'name':'Abc12'}, {'id':33,'name':'Abc123'},{'id':34,'name':'Abc'}]";
        return response()->json($formatted_users);

    }

    /*
    public function getAzureEmail($id)
    {
        $tokenCache = new TokenCache();
        $accessToken = $tokenCache->getAccessToken();
    
        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        
        $user = $graph->createRequest('GET', '/users/' . $id)
            ->setReturnType(Model\User::class)
            ->execute();

        return $user->getMail();
       
    }
    */


}
