<?php

return [
    'status' => [
        'active' => [
            'color' => 'primary',
            'tooltip' => 'Currently in progress or scheduled for a future date',
        ],
        'achieved' => [
            'color' => 'success',
            'tooltip' => 'Supervisor and employee agree objectives met',
        ],
        // 'not met' => [
        //     'color' => 'red',
        //     'tooltip' => 'Substantial portion incomplete by end date',
        // ],
        // 'cancelled or deferred' => [
        //     'color' => 'warning',
        //     'tooltip' =>
        //         'Shift in plans but want to archive goal for future reference',
        // ],
        'archived' => [
            'color' => 'warning',
            'tooltip' =>
                'Cancelled, deferred or no longer relevant to your work but you want to save for future reference',
        ],
    ],

    'conversation' => [
        'topic' => [
            // Performance Check-in
            '1' => [
                'questions' => [
                    '<p>Participants can choose some or all the questions below to help guide discussions. Significant outcomes and action items should be captured in the comment boxes throughout the template.</p>',
                    '<li>Overall, how would you describe your work since our last check-in?</li>',
                    '<li>What progress have you made against your goals?</li>',
                    '<li>Have your goals shifted?Tell me about that.</li>',
                    '<li>What accomplishments are you most proud of?</li>',
                    '<ul><li>How would you like to celebrate success? Do you prefer one-on-one discussions or would you like more public or team-oriented recognition?</li></ul>',
                    '<li>How do you think your role helps the work unit succeed?</li>',
                    '<li>What challenges have you faced? Did you learn anything?</li>',
                    '<ul><li>Is there anything that our team or organization could learn from that you would be open to sharing?</li></ul>',
                    '<li>What support do you need from me as your supervisor to perform at your best?</li>',
                    '<ul><li>What do I do that is most/least helpful for you when it comes to completing your work?</li></ul>',
                    '<li>In what areas do you need or want to improve? What would help you improve?</li>',
                    '<ul><li>Are there specific skills or competencies we should focus on moving forward?</li></ul>',
                    '<li>What motivates you to get your job done?</li>',
                    '<li>Which job responsibilities/tasks do you enjoy most? Which do you least enjoy?</li>',
                    '<li>What opportunities are you looking for moving forward?</li>',
                    '<ul><li>Do you have any interests or skills we could consider integrating into your work?</li></ul>',
                    '<li>How do you prefer to receive feedback and/or recognition for your work?</li>',
                    '<li>What (if any) concerns do you have when it comes to giving me feedback? How can I alleviate those concerns?</li>',
                  ],
                'capture1' => ['Appreciation – highlight what has gone well'],
                'capture2' => ['Coaching – identify areas where things could be (even) better'],
                'capture3' => ['Evaluation – provide an overall summary of performance'],
                'capture4' => [''],
            ],
            // Goal Setting
            '2' => [
                'questions' => [
                    '<p>Supervisors should share relevant team goals and priorities to begin the conversation. Participants can choose some or all the questions below to help guide discussion. Significant outcomes and action items should be captured in the comment boxes throughout the template.</p>',
                    '<li>What goals can you add to your plan to help achieve team priorities?</li>',
                    '<li>What competencies and values will you focus on to achieve your goals?</li>',
                    '<li>What do you need to learn to achieve your goals? Do you have any gaps that you want to address?</li>',
                    '<li>What are the barriers to your success? How will you overcome them?</li>',
                    '<li>Imagine what success will look like: this can inform your personal performance measures (remember to pick results that are within your control and/or influence)</li>',
                    '<li>What personal goals would you like to include in your profile?</li>',
                    '<ul><li>What are your greatest growth opportunities?</li></ul>',
                    '<ul><li>What strengths do you have that you want to use more of? (Think about what you do effortlessly. What are you doing when you are at your best?)</li></ul>',
                    '<ul><li>How do your personal goals align with our team goas or those of the organization?</li></ul>',
                    '<li>What support do you need to meet your goals?</li>',
                ],
                'capture1' => [''],
                'capture2' => [''],
                'capture3' => [''],
                'capture4' => [''],
            ],
            // Career Development
            '3' => [
                'questions' => [
                    '<p>Participants can choose some or all the questions below to help guide discussions. Significant outcomes and action items should be captured in the comments section of the template.</p>',
                    '<li>What are your career goals?</li>',
                    '<ul><li>What do you think you need to get there?</li></ul>',
                    '<li>Do you have specific goals you&rsquo;d like to achieve in the next year, two years, longer?</li>',
                    '<li>Are you interested in exploring other positions or opportunities within the BCPS?</li>',
                    '<ul><li>What do you want your next position to be?</li></ul>',
                    '<li>How do you see your career in the BCPS developing?</li>',
                    '<li>How would you define &ldquo;success&rdquo; for your career?</li>',
                    '<li>What positive impact do you want your career to have on the Public Service (both short term and long term)?</li>',
                    '<li>In what areas do you want to grow?</li>',
                    '<ul><li>What can I and/or the organization do to help support this growth?</li></ul>',
                    '<li>What is one thing I can do to support your career development?</li>',
                    '<li>What is one thing you can do to support your own career development?</li>',
                ],
                'capture1' => [''],
                'capture2' => [''],
                'capture3' => [''],
                'capture4' => [''],
            ],
            // Performance Improvement
            '4' => [
                'questions' => [
                    '<p>Supervisors should summarize the high-level performance expectations identified in the performance profile as requiring further development to begin the conversation. Participants can then use the items below to guide discussion. Significant outcomes and action items should be captured in the appropriate comment boxes throughout the template.</p>',
                    '<li>Tell me about how things have been going for you in your role.</li>',
                    '<ul><li>What is going well?</li></ul>',
                    '<ul><li>Where do you see opportunities for improvement?</li></ul>',
                    '<li>To meet these expectations, what support do you need?</li>',
                    '<ul><li>This could be coaching, tools, resources, additional training, etc.</li></ul>',
                    '<li>Between now and our next conversation, I would like you to work on 2-3 areas we&rsquo;ve discussed as requiring development. What specific steps will you take to meet these expectations?</li>',
                    '<li>We will follow up on these areas and discuss your progress during our next meeting. If needed, we can discuss an action plan for improvement in each of those areas when we meet again.</li>',
                    '<li>Is there anything else you would like to share with me or any other reasonable support that I can offer?</li></ul>',
                ],
                'capture1' => ['What date will a follow up meeting occur?'],
                'capture2' => ['What must the employee accomplish? By when?'],
                'capture3' => ['What support will the supervisor (and others) provide? By when?'],
                'capture4' => [''],
            ],
            // Recognition
            // '5' => [
            //     'questions' => [
            //         'Supervisor: Provide a verbal overview of the actions or results being celebrated. Be as specific as possible about timing, activities, and outcomes achieved. Highlight behaviours, competencies, and corporate values that you feel contributed to the success. Connect the work to the goals and/or values of the organization.',
            //         'What aspect of this accomplishment are you most proud of?',
            //         'What challenges did you face? Did you learn anything? Is there anything that our team or organization could learn from that you would be open to sharing?',
            //         'Who else contributed to your success?',
            //         'How would you like to celebrate success? Do you prefer one-on-one discussions or would you like more public or team-oriented recognition?',
            //     ],
            // ],
            // Onboarding
            // '6' => [
            //     'questions' => [
            //         'Do you have a clear understanding of the expectations for this role?',
            //         'Do you feel like you’ve received access to all of the information, tools, and resources you need to complete your responsibilities? What additional supports or information do you need? Are there any specific tools or training sessions that you think would help you be more successful?',
            //         'What would help you feel connected to the rest of the team?',
            //         'What, if anything, is still unclear to you in terms of your duties and our work policies?',
            //         'What roadblocks or challenges, if any, have you encountered while training or when performing your duties? ',
            //         'Moving forward, what would you like to accomplish in the next 30 days? 60 days? 90 days?',
            //         'Do you have any expertise or experience that you think could be better utilized?',
            //         'How well do you understand the Corporate Plan, your ministry’s vision, mission and mandate etc.?',
            //         'What supports do you need from me as your supervisor?',
            //         'How do you prefer to receive feedback and/or recognition for your work?',
            //         'Is there anything else you’d like for me to know?',
            //         'Is there anything you would like more information on or to understand better?',
            //         'What would be the best use of our one-on-one time?',
            //         'What area of the organization would you like to learn more about?',
            //         'Which aspects of the job are you excited about? Which are you worried about?',
            //     ],
            // ],
            '7' => [
                'questions' => [
                    'Tell me about how things have been going for you in your role. ',
                    'What accomplishments are you most proud of? How do you think your role helps the work unit succeed?',
                    'What challenges have you faced? Did you learn anything? Is there anything that our team or organization could learn from that you would be open to sharing?',
                    'Supervisor: Verbally summarize the high-level performance expectations for the role and identify areas of strength and areas that require further development.',
                    'Do you feel like you’ve received access to all of the information, tools, and resources you need to complete your responsibilities? What additional supports or information do you need? Are there any specific tools or training sessions that you think would help you be more successful?',
                    'What, if anything, is still unclear to you in terms of your duties and our work policies?',
                    'Which job responsibilities/tasks do you enjoy most? Which do you least enjoy?',
                    'Are you finding anything difficult or stressful?',
                    'What opportunities are you looking for moving forward? Do you have any interests or skills we could consider integrating into your work?',
                    'What strengths do you want to use more of? (Think about what you do effortlessly. What are you doing when you are at your best?)',
                    'What are your greatest growth opportunities?',
                ],
            ],
        ],
    ],
];
