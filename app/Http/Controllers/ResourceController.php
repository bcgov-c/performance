<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Tag;

class ResourceController extends Controller
{
    public function userguide(Request $request)
    {   
        $t = $request->t;

        $data = [
            [
                'question' => 'Welcome!',
                'answer_file' => '5'
            ],
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
        return view('resource.user-guide', compact('data', 't'));
    }
    public function videotutorials(Request $request)
    {   
        $t = $request->t;

        $data = [
            [
                'question' => 'Video Tutorials',
                'answer_file' => '1'
            ],
        ];
        return view('resource.video-tutorials', compact('data', 't'));
    }
    public function goalsetting(Request $request)
    {
        
        $t = $request->t;
        
        //get goal tags
        $tags = Tag::all()->sortBy("name")->toArray();
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
            'question' => 'How to use the conversation templates',
            'answer_file' => '0'
          ],
          [
              'question' => 'Why are performance conversations important?',
              'answer_file' => '2'
          ],
          [
              'question' => 'What makes a conversation effective?',
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
              'question' => 'Asking for feedback or inquiring into someone else\'s perspective',
              'answer_file' => '6'
          ],     
          [
              'question' => 'Addressing a performance issue',
              'answer_file' => '7'
          ],      
      ];
         return view('resource.conversations', compact('data', 't'));
    }
    public function contact(Request $request)
    {
        $t = $request->t;

        $data = [
            [
                'question' => 'Agriculture and Food',
                'answer_file' => "0"
            ],
            [
                'question' => 'Attorney General',
                'answer_file' => "1"
            ],
            [
              'question' => 'BC Public Service Agency',
              'answer_file' => "2"
            ],
            [
                'question' => 'Children and Family Development',
                'answer_file' => "3"
            ],
            [
                'question' => 'Citizens’ Services',
                'answer_file' => "4"
            ],
            [
                'question' => 'Education and Child Care',
                'answer_file' => "5"
            ],
            [
                'question' => 'Emergency Management and Climate Readiness',
                'answer_file' => "6"
            ],
            [                                                                                                                                         
                'question' => 'Energy, Mines and Low Carbon Innovation',
                'answer_file' => "7"
            ],
            [
                'question' => 'Environment and Climate Change Strategy',
                'answer_file' => "8"
            ],
            [
                'question' => 'Finance',
                'answer_file' => "9"
            ],
            [
                'question' => 'Forests',
                'answer_file' => "10"
            ],
            [
                'question' => 'Government Communications & Public Engagement',
                'answer_file' => "11"
            ],
            [
                'question' => 'Health',
                'answer_file' => "12"
            ],
            [
                'question' => 'Housing',
                'answer_file' => "13"
            ],
            [
                'question' => 'Indigenous Relations and Reconciliation',
                'answer_file' => "14"
            ],
            [
                'question' => 'Jobs, Economic Development and Innovation',
                'answer_file' => "15"
            ],
            [
                'question' => 'Labour',
                'answer_file' => "16"
            ],
            [
                'question' => 'Mental Health and Addictions',
                'answer_file' => "17"
            ],
            [
                'question' => 'Municipal Affairs',
                'answer_file' => "18"
            ],
            [
                'question' => 'Post-Secondary Education and Future Skills',
                'answer_file' => "19"
            ],
            [
                'question' => 'Premier’s Office',
                'answer_file' => "20"
            ],
            [
                'question' => 'Public Safety and Solicitor General',
                'answer_file' => "21"
            ],
            [
                'question' => 'Royal BC Museum',
                'answer_file' => "22"
            ],
            [
                'question' => 'Social Development and Poverty Reduction',
                'answer_file' => "23"
            ],
            [
                'question' => 'Tourism, Arts, Culture and Sport',
                'answer_file' => "24"
            ],
            [
                'question' => 'Transportation and Infrastructure',
                'answer_file' => "25"
            ],
            [
                'question' => 'Water, Land and Resource Stewardship',
                'answer_file' => "26"
            ],
        ];
        return view('resource.contact', compact('data', 't'));
    }

    public function faq(Request $request)
    {
      
      $t = $request->t;

      $data = [
        [
            'question' => 'Why is performance development important?',
            'answer_file' => "0"
        ],
        [
            'question' => 'How often do I need to have performance conversations?',
            'answer_file' => "1"
        ],
        [
            'question' => 'How do I add a goal to my PDP profile?',
            'answer_file' => "2"
        ],
        [
            'question' => 'How do I share a goal with a colleague?',
            'answer_file' => "3"
        ],
        [
            'question' => 'How do I set up a performance conversation with my employee / supervisor?',
            'answer_file' => "4"
        ],
        [
            'question' => 'I am a supervisor. How do I create a goal in my team’s Goal Bank?',
            'answer_file' => "5"
        ],
        [
            'question' => 'I’m a supervisor. I want to view my employee’s goals and upcoming conversation deadlines. How do I do this?',
            'answer_file' => "6"
        ],
        [
            'question' => 'I do not see the correct supervisor in the PDP / I do not see the correct direct reports in the PDP. What do I do?',
            'answer_file' => "7"
        ],
        [
            'question' => 'Is there an autosave function on the application?',
            'answer_file' => "8"
        ],
        [
            'question' => 'Is there a timeout on the application?',
            'answer_file' => "9"
        ],
        [
            'question' => 'Can I access the PDP from home?',
            'answer_file' => "10"
        ],
        [
            'question' => 'What notifications and reminders will the PDP send me? Can I choose how often to receive an email from the PDP?',
            'answer_file' => "11"
        ],
        [
            'question' => 'Who can assist me if I need help with the PDP?',
            'answer_file' => "12"
        ],
        [
            'question' => 'How do I access past MyPerformance files?',
            'answer_file' => "13"
        ],
        [
            'question' => 'Why are there no ratings in the new approach to performance development?',
            'answer_file' => "14"
        ],
        [
            'question' => 'How can I qualify for a Pacific Leaders Scholarship if there are no ratings as part of performance development?',
            'answer_file' => "15"
        ],
        [
            'question' => 'How will I qualify for MCCF in-range compensation movement if there are no ratings as part of performance development?',
            'answer_file' => "16"
        ],
        [
            'question' => 'What do I do if one of my employees is not performing up to expectations?',
            'answer_file' => "17"
        ],
        [
            'question' => 'What happens when an employee clicks “I disagree with the information contained in this performance review”?',
            'answer_file' => "18"
        ],
        [
            'question' => 'I am a supervisor and my employee clicked “I disagree with the information contained in this performance review”. What do I do?',
            'answer_file' => "19"
        ],
        [
            'question' => 'I am a supervisor and I don’t have enough time in my schedule to go through this process with my employees. Why do we need to do this?',
            'answer_file' => "20"
        ],
        [
            'question' => 'I support a ministry and need to have HR Administrator access for the PDP. How do I receive this status?',
            'answer_file' => "21"
        ],
      ];
         return view('resource.faq', compact('data', 't'));
    }
}
