<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Tag;

class ResourceController extends Controller
{
    public function userguide(Request $request)
    {        
        $data = [
            [
                'question' => 'My Goals Section',
                'answer_file' => '2'
            ],
            [
                'question' => 'My Conversations Section',
                'answer_file' => '3'
            ],
            [
                'question' => 'My Team Section (supervisors only)',
                'answer_file' => '4'
            ],

        ];
        return view('resource.user-guide', compact('data'));
    }
    public function goalsetting(Request $request)
    {
        //get goal tags
        $tags = Tag::all()->toArray();
        $t = $request->t;
        
        $data = [
            [
                'question' => 'What is goal setting?',
                'answer' => 'Goal setting is a process of working towards what we want to do or who we want to be. Employees and supervisors should collaborate and communicate openly on what goals should be and how they can be achieved.'
            ],
            [
                'question' => 'Why are goals important?',
                'answer_file' => '2'
            ],
            [
                'question' => 'SMART and HARD goal setting frameworks',
                'answer_file' => '3'
            ],
            [
                'question' => 'What does a good goal statement look like?',
                'answer_file' => '4'
            ],
            [
                'question' => 'Tips on how to get started',
                'answer_file' => '9'
            ],            
            [
                'question' => 'What are goal tags?',
                'answer_file' => '8'
            ],
            [
                'question' => 'Examples of Work Goals',
                'answer_file' => '5'
            ],
            [
                'question' => 'Examples of Learning Goals',
                'answer_file' => '6'
            ],
            [
                'question' => 'Examples of Career Goals',
                'answer_file' => '7'
            ],
        ];
        return view('resource.goal-setting', compact('data', 'tags', 't'));
    }
    public function conversations(Request $request)
    {
      
      $t = $request->t;

      $data = [
          [
              'question' => 'What is a performance development conversation?',
              'answer' => "Any conversation about an employee and their work can be considered a performance development conversation. They can be informal check-ins, regular 1-on-1's, recognition for a job well done, feedback, or more formal conversations when trying to modify behaviour."
          ],
          [
              'question' => 'Why are conversations important?',
              'answer_file' => '2'
          ],
          [
              'question' => 'When are conversations effective?',
              'answer_file' => '3'
          ],
          [
              'question' => 'Elements of a meaningful conversation',
              'answer_file' => '4'
          ],
          [
              'question' => 'Elements of effective feedback',
              'answer_file' => '5'
          ],
          [
              'question' => 'How to use the conversation templates',
              'answer_file' => '6'
          ],          
      ];
         return view('resource.conversations', compact('data', 't'));
    }
    public function contact()
    {
         return view('resource.contact');
    }
}
