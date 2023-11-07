<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;

class ResourceController extends Controller
{
    public function userguide(Request $request)
    {   
        $t = $request->t;
        $data = $this->pullContent('userguide');             

        return view('resource.user-guide', compact('data', 't'));
    }
    public function videotutorials(Request $request)
    {   
        $t = $request->t;

        $data = $this->pullContent('videotutorials');       
        return view('resource.video-tutorials', compact('data', 't'));
    }
    public function goalsetting(Request $request)
    {
        
        $t = $request->t;
        
        //get goal tags
        $tags = Tag::all()->sortBy("name")->toArray();
        $data = $this->pullContent('goalsetting');       
        return view('resource.goal-setting', compact('data', 'tags', 't'));
    }
    public function conversations(Request $request)
    {
      
        $t = $request->t;

        $data = $this->pullContent('conversations');      
        return view('resource.conversations', compact('data', 't'));
    }
    public function contact(Request $request)
    {
        $t = $request->t;

        $data = $this->pullContent('contact');      
        return view('resource.contact', compact('data', 't'));
    }

    public function faq(Request $request)
    {
      
      $t = $request->t;

      $data = $this->pullContent('faq');  
      return view('resource.faq', compact('data', 't'));
    }


    public function hradmin(Request $request)
    {   
        $t = $request->t;
        $data = $this->pullContent('hr-admin');  
        
        return view('resource.hr-admin', compact('data', 't'));
    }

    private function pullContent($category){
        $resourceData = DB::table('resource_content')
            ->select('question', 'answer', 'answer_file')
            ->where('category', $category)
            ->get();

        // Initialize an empty array to store the formatted data
        $data = [];

        // Loop through the retrieved data and format it
        foreach ($resourceData as $row) {
            $data[] = [
                'question' => $row->question,
                'answer' => $row->answer,
                'answer_file' => $row->answer_file,
            ];
        }   
        return $data;
    }
}
