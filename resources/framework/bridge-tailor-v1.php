<?php

/*
|--------------------------------------------------------------------------
| Bridge-Tailor Business Diagnostic Framework — v1
|--------------------------------------------------------------------------
|
| This file is the single source of truth for the diagnostic methodology.
| It merges the Bridge-Tailor client diagnostic (symptom intake with pain
| ratings, evidence "gateway" questions, feel-vs-evidence blending,
| programme routing, finance-before-capital gating and cross-area insight
| rules) with the five-module assessment structure.
|
| The seeder loads it into the `framework_versions` table. Administrators
| can then edit the JSON in the admin area and publish new versions; every
| assessment is pinned to the version it was taken against, so historical
| scores remain reproducible.
|
| Answer convention: every evidence question has three options ordered
| best → worst (In place / Partly in place / Missing). Scores come from
| scoring.answer_scores, never from the AI.
*/

return [
    'version' => '1.0.0',
    'name' => 'Bridge-Tailor Business Diagnostic Framework',

    'scoring' => [
        // Equal blend of "how it feels" (pain ratings) and "what the evidence shows".
        'feel_weight' => 0.5,
        // A gap this large between feel and evidence is flagged as a blind spot.
        'perception_gap' => 15,
        // Points for answer index 0 (in place), 1 (partial), 2 (missing).
        'answer_scores' => [100, 50, 0],
        // Severity thresholds on the 0–100 health scale.
        'severity' => ['low_from' => 67, 'medium_from' => 33],
        // Business Health Index bands. Deliberately distinct from the paid HealthCheck maturity ladder.
        'index_bands' => [
            ['min' => 0,  'max' => 29,  'key' => 'strain',    'label' => 'Under real strain', 'color' => '#7A3B06',
             'note' => 'What you raised is causing active damage, not just friction.'],
            ['min' => 30, 'max' => 49,  'key' => 'stretched', 'label' => 'Stretched',         'color' => '#B85A0A',
             'note' => 'The business is working, but on effort rather than on system.'],
            ['min' => 50, 'max' => 69,  'key' => 'holding',   'label' => 'Holding up',        'color' => '#C9790F',
             'note' => 'The foundations are there. They are inconsistent rather than absent.'],
            ['min' => 70, 'max' => 100, 'key' => 'solid',     'label' => 'Largely solid',     'color' => '#2F5E76',
             'note' => 'No area is in difficulty on what you have told us. Worth confirming properly.'],
        ],
        'area_labels' => [
            ['min' => 0,  'max' => 29,  'label' => 'Serious'],
            ['min' => 30, 'max' => 49,  'label' => 'Strained'],
            ['min' => 50, 'max' => 69,  'label' => 'Mixed'],
            ['min' => 70, 'max' => 100, 'label' => 'Sound'],
        ],
        'soft_severity' => ['Low' => 'Worth watching', 'Medium' => 'Needs attention', 'High' => 'Priority'],
    ],

    'stages' => [
        'solo'   => 'Solo founder / pre-formalisation',
        'growth' => 'Growth stage',
        'mature' => 'Mature / family, multi-generation',
    ],

    'sectors' => [
        'general'       => 'General / other',
        'oil_gas'       => 'Oil, gas & energy',
        'agritech'      => 'Agriculture / agritech',
        'fintech'       => 'Fintech / financial services',
        'manufacturing' => 'Manufacturing & processing',
        'retail'        => 'Retail, wholesale & FMCG',
        'services'      => 'Professional services',
        'construction'  => 'Construction & real estate',
        'ict'           => 'ICT & technology',
        'healthcare'    => 'Healthcare & pharmaceuticals',
        'hospitality'   => 'Hospitality & tourism',
        'logistics'     => 'Transport & logistics',
        'education'     => 'Education',
    ],

    'revenue_bands' => [
        'lt1'    => 'Under GHS 1M',
        '1to3'   => 'GHS 1M – 3M',
        '3to6'   => 'GHS 3M – 6M',
        '6to10'  => 'GHS 6M – 10M',
        '10to20' => 'GHS 10M – 20M',
        'gt20'   => 'Over GHS 20M',
        'na'     => 'Prefer not to say',
    ],

    'size_bands' => [
        '1-5' => '1 – 5 staff', '6-20' => '6 – 20 staff', '21-50' => '21 – 50 staff',
        '51-200' => '51 – 200 staff', '200+' => 'More than 200 staff',
    ],

    /*
    | Board-approved programme catalogue. Each criterion routes to one business
    | line and its entry programme, with the expansion product behind it.
    | Prices are internal (consultant view only).
    */
    'programmes' => [
        'strategy' => [
            'line' => 'Business Strategy', 'stage' => 'Stage 1 · Diagnose',
            'entry' => 'Scale Readiness Review', 'entry_price' => 'GHS 8,000 – 15,000', 'entry_duration' => '4 weeks',
            'next' => 'Governance & Structure Programme', 'next_price' => 'GHS 25,000 – 40,000',
            'revenue_type' => 'Project Fee', 'track' => 'Growth & Governance Impact',
            'metric' => '% of recommendations adopted within 90 days',
            'effort' => ['Low' => [4, 6], 'Medium' => [8, 12], 'High' => [12, 20]],
        ],
        'performance' => [
            'line' => 'Business Performance', 'stage' => 'Stage 2 · Optimise',
            'entry' => 'Performance System Setup', 'entry_price' => 'GHS 15,000 – 25,000', 'entry_duration' => '6 weeks',
            'next' => 'Finance Desk (retainer)', 'next_price' => 'GHS 2,000 – 18,000 / month by tier',
            'revenue_type' => 'Project Fee → Fixed Retainer', 'track' => 'Performance Impact',
            'metric' => 'Cost/time saved · compliance score · cycle-time reduction',
            'effort' => ['Low' => [3, 5], 'Medium' => [6, 10], 'High' => [10, 16]],
        ],
        'finance_access' => [
            'line' => 'Finance Access', 'stage' => 'Stage 3 · Fund',
            'entry' => 'Capital Preparation Programme', 'entry_price' => 'GHS 25,000 – 40,000', 'entry_duration' => '8 weeks',
            'next' => 'Investment Readiness Sprint', 'next_price' => 'GHS 18,000 – 28,000',
            'revenue_type' => 'Project Fee', 'track' => 'Capital Impact',
            'metric' => '$ raised or structured · working capital unlocked',
            'effort' => ['Low' => [6, 8], 'Medium' => [10, 14], 'High' => [14, 22]],
        ],
        'market_access' => [
            'line' => 'Market Access', 'stage' => 'Stage 4 · Expand',
            'entry' => 'Market Intelligence Report', 'entry_price' => 'GHS 8,000 – 15,000', 'entry_duration' => '4 weeks',
            'next' => 'Market Entry Package', 'next_price' => 'GHS 30,000 – 60,000',
            'revenue_type' => 'Project Fee', 'track' => 'Market Impact',
            'metric' => 'New-market revenue · partnerships signed · approvals secured',
            'effort' => ['Low' => [5, 7], 'Medium' => [8, 12], 'High' => [12, 18]],
        ],
    ],

    'delivery_modes' => ['Low' => 'Light advisory', 'Medium' => 'Advisory + embedded support', 'High' => 'Full embedded delivery'],

    // Capital doesn't convert on shaky books: financial control is sequenced before capital raising.
    'sequencing' => [
        'gates' => [
            ['first' => 'performance', 'before' => 'finance_access', 'unless_severity' => 'Low',
             'reason' => 'Getting the financial base right has to come before raising capital — capital doesn\'t convert on shaky books.'],
        ],
    ],

    'symptom_categories' => [
        'A' => 'Leadership and Governance',
        'B' => 'Financial Confidence',
        'C' => 'Capital & Funding',
        'D' => 'Growth & Market',
        'F' => 'Operations & Systems',
        'G' => 'People & Performance',
        'E' => 'Not sure — it just feels off',
    ],

    // Each symptom points at one or more modules with a strength (1 = direct hit).
    'symptoms' => [
        ['id' => 's1',  'cat' => 'A', 'label' => 'There is no clearly defined internal structure', 'modules' => ['SG' => 1, 'HC' => 0.6]],
        ['id' => 's2',  'cat' => 'A', 'label' => 'I can\'t step away without things falling apart', 'modules' => ['SG' => 1]],
        ['id' => 's3',  'cat' => 'A', 'label' => 'Growth potential isn\'t translating into the business', 'modules' => ['SG' => 1, 'MG' => 0.6]],
        ['id' => 's4',  'cat' => 'A', 'label' => 'There is no clear succession plan', 'modules' => ['SG' => 1]],
        ['id' => 's5',  'cat' => 'B', 'label' => 'I don\'t fully trust the numbers I\'m given', 'modules' => ['FH' => 1]],
        ['id' => 's6',  'cat' => 'B', 'label' => 'We\'ve had a tax or compliance surprise', 'modules' => ['FH' => 1, 'OE' => 0.6]],
        ['id' => 's7',  'cat' => 'B', 'label' => 'I don\'t know if we\'re profitable line by line', 'modules' => ['FH' => 1]],
        ['id' => 's8',  'cat' => 'B', 'label' => 'Cash feels tight even though sales look fine', 'modules' => ['FH' => 1, 'SG' => 0.6]],
        ['id' => 's9',  'cat' => 'C', 'label' => 'We keep getting rejected or delayed on funding', 'modules' => ['FH' => 1]],
        ['id' => 's10', 'cat' => 'C', 'label' => 'We want to raise capital but don\'t know if we\'re ready', 'modules' => ['FH' => 1]],
        ['id' => 's11', 'cat' => 'C', 'label' => 'Funders have asked for documents we don\'t have', 'modules' => ['FH' => 1]],
        ['id' => 's12', 'cat' => 'C', 'label' => 'We struggle in presenting our business to investors', 'modules' => ['FH' => 1, 'SG' => 0.4]],
        ['id' => 's13', 'cat' => 'D', 'label' => 'We expanded and it isn\'t working', 'modules' => ['MG' => 1]],
        ['id' => 's14', 'cat' => 'D', 'label' => 'We want to grow but don\'t know which market to prioritise', 'modules' => ['MG' => 1]],
        ['id' => 's15', 'cat' => 'D', 'label' => 'We\'re facing regulations we don\'t understand', 'modules' => ['MG' => 1, 'SG' => 0.4]],
        ['id' => 's16', 'cat' => 'D', 'label' => 'We need local partners and don\'t know how to vet them', 'modules' => ['MG' => 1]],
        ['id' => 's19', 'cat' => 'F', 'label' => 'Work gets redone or falls through the cracks between teams', 'modules' => ['OE' => 1]],
        ['id' => 's20', 'cat' => 'F', 'label' => 'We can\'t tell which activities cost us the most time or money', 'modules' => ['OE' => 1, 'FH' => 0.4]],
        ['id' => 's21', 'cat' => 'F', 'label' => 'Our systems are manual or spreadsheets that don\'t talk to each other', 'modules' => ['OE' => 1]],
        ['id' => 's22', 'cat' => 'G', 'label' => 'Good people leave and we struggle to replace them', 'modules' => ['HC' => 1]],
        ['id' => 's23', 'cat' => 'G', 'label' => 'We don\'t really know who is performing and who isn\'t', 'modules' => ['HC' => 1]],
        ['id' => 's24', 'cat' => 'G', 'label' => 'Staff wait to be told what to do', 'modules' => ['HC' => 1, 'SG' => 0.6]],
        ['id' => 's17', 'cat' => 'E', 'label' => 'The business feels stuck despite the effort', 'modules' => ['SG' => 0.8, 'FH' => 0.8]],
        ['id' => 's18', 'cat' => 'E', 'label' => 'We had a crisis and want to prevent a repeat', 'modules' => ['FH' => 0.8, 'SG' => 0.8]],
    ],

    'module_order' => ['SG', 'FH', 'OE', 'MG', 'HC'],
    'modules' => [

        /* ------------------------------------------------------------ */
        'SG' => [
            'name' => 'Corporate Strategy & Governance',
            'short' => 'Strategy & Governance',
            'description' => 'Strategy and vision, structure, governance, leadership, decision rights and risk management.',
            'weight' => 1,
            'ai_instructions' => 'Assess whether direction, structure and decision rights exist outside the founder. Treat founder dependency as a valuation and continuity risk, not a culture observation. Distinguish missing governance (structure absent) from weak execution (structure present but not used).',
            'criteria' => [
                'SG1' => ['name' => 'Business strategy and vision', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'sg_1', 'text' => 'Is there a written strategy with 2–3 year targets that the leadership team works to?',
                     'options' => ['Yes, written and reviewed', 'Ideas exist, not written down', 'No defined strategy'], 'risk' => 'high'],
                    ['code' => 'sg_2', 'text' => 'Are annual priorities translated into budgets and named owners?',
                     'options' => ['Yes, consistently', 'Partly', 'No']],
                ]],
                'SG2' => ['name' => 'Organisational structure', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'fd4', 'text' => 'Are roles and decision rights written down anywhere?',
                     'options' => ['Yes, clearly', 'Partially', 'Not really']],
                    ['code' => 'fd2', 'text' => 'Do senior team members make decisions without checking with you first?',
                     'options' => ['Regularly', 'Sometimes', 'Rarely or never'], 'skip_stages' => ['solo']],
                ]],
                'SG3' => ['name' => 'Corporate governance', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'sg_3', 'text' => 'Does the business have a board or advisory board that meets on a fixed schedule?',
                     'options' => ['Yes, meets regularly with minutes', 'Exists but meets irregularly', 'No board or advisers'], 'skip_stages' => ['solo']],
                    ['code' => 'sg_4', 'text' => 'Are statutory filings (company registry, beneficial ownership, annual returns) up to date?',
                     'options' => ['Yes, all current', 'Some are behind', 'Not sure / several behind'], 'risk' => 'critical'],
                ]],
                'SG4' => ['name' => 'Leadership and decision-making', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'fd1', 'text' => 'If you were unreachable for two weeks, would the business run normally?',
                     'options' => ['Yes, fully', 'Mostly, with some gaps', 'No, it would stall'], 'risk' => 'high'],
                    ['code' => 'sg_f1', 'text' => 'Is there a named deputy who can approve spending and hiring in your absence?',
                     'options' => ['Yes, with written limits', 'Informally', 'No'], 'show_if' => ['q' => 'fd1', 'min' => 1]],
                    ['code' => 'fd3', 'text' => 'Is there a documented plan for who leads if something happens to you?',
                     'options' => ['Yes, documented', 'Informal, not written down', 'No plan'], 'skip_stages' => ['solo'], 'risk' => 'high'],
                ]],
                'SG5' => ['name' => 'Risk management', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'sg_5', 'text' => 'Do you keep a list of the main risks to the business and review it at least quarterly?',
                     'options' => ['Yes, reviewed regularly', 'Informally', 'No']],
                    ['code' => 'sg_6', 'text' => 'Are key assets, people and contracts insured or otherwise protected?',
                     'options' => ['Yes, reviewed annually', 'Partly', 'No / not sure'], 'risk' => 'high'],
                ]],
            ],
        ],

        /* ------------------------------------------------------------ */
        'FH' => [
            'name' => 'Financial Health & Investment Readiness',
            'short' => 'Financial Health',
            'description' => 'Financial management, cash flow, profitability, access to finance and investment readiness.',
            'weight' => 1,
            'ai_instructions' => 'Never invent or estimate revenue, profit, cash or valuation figures. Only cite figures that appear in client answers or extracted documents, and label them as client-provided and unaudited. Treat weak financial control as gating any capital raise. Separate reporting gaps (fixable through a monthly close) from fundraising gaps.',
            'criteria' => [
                'FH1' => ['name' => 'Financial management', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'fc1', 'text' => 'How confident are you this month\'s numbers are accurate?',
                     'options' => ['Very', 'Somewhat', 'Not very'], 'risk' => 'high'],
                    ['code' => 'fh_1', 'text' => 'Are management accounts produced within 15 days of each month end?',
                     'options' => ['Yes, every month', 'Occasionally or late', 'No']],
                    ['code' => 'fc2', 'text' => 'Have you had a tax or compliance surprise in the last 2 years?',
                     'options' => ['No', 'One', 'More than one'], 'risk' => 'critical'],
                ]],
                'FH2' => ['name' => 'Cash flow management', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'fc4', 'text' => 'Is cash ever tight even when sales look fine?',
                     'options' => ['Rarely', 'Sometimes', 'Often'], 'risk' => 'high'],
                    ['code' => 'fh_2', 'text' => 'Do you keep a forward-looking cash-flow forecast (at least 13 weeks)?',
                     'options' => ['Yes, updated weekly or monthly', 'Occasionally', 'No']],
                    ['code' => 'fh_f1', 'text' => 'How long, on average, do customers take to pay you?',
                     'options' => ['Under 30 days', '30 – 60 days', 'Over 60 days / not tracked'], 'show_if' => ['q' => 'fc4', 'min' => 1]],
                ]],
                'FH3' => ['name' => 'Profitability and financial sustainability', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'fc3', 'text' => 'Do you know your margin by product or service line?',
                     'options' => ['Yes, tracked', 'Roughly', 'No']],
                    ['code' => 'fh_3', 'text' => 'Is pricing reviewed against actual costs at least once a year?',
                     'options' => ['Yes', 'Sometimes', 'No']],
                ]],
                'FH4' => ['name' => 'Access to finance', 'programme' => 'finance_access', 'weight' => 1, 'questions' => [
                    ['code' => 'ir2', 'text' => 'Have you been rejected or delayed by a funder before?',
                     'options' => ['No', 'Once', 'Multiple times']],
                    ['code' => 'ir_f1', 'text' => 'Have the reasons the funder gave since been addressed?',
                     'options' => ['Yes, addressed', 'Partly', 'No / reasons unknown'], 'show_if' => ['q' => 'ir2', 'min' => 1]],
                    ['code' => 'ir4', 'text' => 'Do you know which funder type you\'re targeting and what they screen for?',
                     'options' => ['Yes', 'Roughly', 'No']],
                ]],
                'FH5' => ['name' => 'Investment readiness', 'programme' => 'finance_access', 'weight' => 1, 'questions' => [
                    ['code' => 'ir1', 'text' => 'Do you have 2+ years of audited or reviewed financials ready?',
                     'options' => ['Yes', 'Partial', 'No'], 'risk' => 'high'],
                    ['code' => 'ir3', 'text' => 'Do you have a data room or investment memo ready?',
                     'options' => ['Yes', 'In progress', 'No']],
                ]],
            ],
        ],

        /* ------------------------------------------------------------ */
        'OE' => [
            'name' => 'Operational Excellence',
            'short' => 'Operations',
            'description' => 'Business processes, efficiency, resource utilisation, internal controls and technology adoption.',
            'weight' => 1,
            'ai_instructions' => 'Look for work that depends on individuals rather than documented process, missing segregation of duties, and manual systems that create rework. Recommend proportionate controls appropriate to an African SME, not enterprise-grade bureaucracy.',
            'criteria' => [
                'OE1' => ['name' => 'Business processes', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'oe_1', 'text' => 'Are your core processes (sales to delivery to billing) documented?',
                     'options' => ['Yes, documented and followed', 'Some are', 'No, they live in people\'s heads']],
                    ['code' => 'oe_2', 'text' => 'When something goes wrong, is the cause recorded and fixed at the source?',
                     'options' => ['Usually', 'Sometimes', 'Rarely']],
                ]],
                'OE2' => ['name' => 'Operational efficiency', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'oe_3', 'text' => 'Do you track a few operating KPIs (turnaround time, defects, on-time delivery) weekly or monthly?',
                     'options' => ['Yes, reviewed regularly', 'Some, irregularly', 'No']],
                    ['code' => 'sec_mf', 'text' => 'Do you track production yield or waste against a target?',
                     'options' => ['Yes, by line or batch', 'Roughly', 'No'], 'sectors' => ['manufacturing', 'agritech']],
                ]],
                'OE3' => ['name' => 'Resource utilisation', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'oe_4', 'text' => 'Do you know the utilisation of your main assets or billable staff?',
                     'options' => ['Yes, measured', 'Roughly', 'No']],
                    ['code' => 'sec_rt', 'text' => 'Do you know stock turnover and slow-moving stock by product line?',
                     'options' => ['Yes, reviewed monthly', 'Roughly', 'No'], 'sectors' => ['retail', 'manufacturing', 'healthcare']],
                ]],
                'OE4' => ['name' => 'Internal controls', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'oe_5', 'text' => 'Are payments approved by someone other than the person who raises them?',
                     'options' => ['Always', 'Sometimes', 'No'], 'risk' => 'critical', 'skip_stages' => ['solo']],
                    ['code' => 'oe_6', 'text' => 'Are bank accounts reconciled at least monthly?',
                     'options' => ['Yes', 'Occasionally', 'No'], 'risk' => 'high'],
                ]],
                'OE5' => ['name' => 'Technology adoption', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'oe_7', 'text' => 'Do you use an accounting or ERP system rather than spreadsheets alone?',
                     'options' => ['Yes, integrated system', 'Basic software plus spreadsheets', 'Spreadsheets or paper only']],
                    ['code' => 'oe_f1', 'text' => 'Is business data backed up and access restricted by role?',
                     'options' => ['Yes', 'Partly', 'No / not sure'], 'risk' => 'high'],
                ]],
            ],
        ],

        /* ------------------------------------------------------------ */
        'MG' => [
            'name' => 'Market Access & Business Growth',
            'short' => 'Market & Growth',
            'description' => 'Market positioning, customer acquisition, competition, distribution channels and expansion.',
            'weight' => 1,
            'ai_instructions' => 'New markets punish assumption. Distinguish evidenced demand from assumed demand, and verified partners from identified ones. Highlight regulatory and local-content requirements relevant to the sector and country, but state when a requirement needs to be confirmed rather than asserting specifics.',
            'criteria' => [
                'MG1' => ['name' => 'Market positioning', 'programme' => 'market_access', 'weight' => 1, 'questions' => [
                    ['code' => 'mg_1', 'text' => 'Can you state clearly who your target customer is and why they choose you over alternatives?',
                     'options' => ['Yes, with evidence', 'Roughly', 'Not really']],
                ]],
                'MG2' => ['name' => 'Customer acquisition', 'programme' => 'market_access', 'weight' => 1, 'questions' => [
                    ['code' => 'mg_2', 'text' => 'Do you know which channels bring your best customers and what each costs?',
                     'options' => ['Yes, tracked', 'Roughly', 'No']],
                    ['code' => 'mg_3', 'text' => 'How concentrated is revenue in your top three customers?',
                     'options' => ['Under 30% of revenue', '30 – 60%', 'Over 60% / not known'], 'risk' => 'high'],
                ]],
                'MG3' => ['name' => 'Competitive analysis', 'programme' => 'market_access', 'weight' => 1, 'questions' => [
                    ['code' => 'rm4', 'text' => 'Is there evidence of demand beyond assumption?',
                     'options' => ['Strong evidence', 'Some signals', 'Mostly assumption']],
                    ['code' => 'mg_4', 'text' => 'Do you review competitor pricing and offers at least twice a year?',
                     'options' => ['Yes', 'Occasionally', 'No']],
                ]],
                'MG4' => ['name' => 'Distribution channels', 'programme' => 'market_access', 'weight' => 1, 'questions' => [
                    ['code' => 'rm3', 'text' => 'Do you have a local partner or channel identified for your next market?',
                     'options' => ['Yes', 'Identified, not vetted', 'No']],
                ]],
                'MG5' => ['name' => 'Market expansion opportunities', 'programme' => 'market_access', 'weight' => 1, 'questions' => [
                    ['code' => 'rm1', 'text' => 'Have you already tried entering a new market or channel?',
                     'options' => ['Not yet', 'Tried, mixed results', 'Tried, not working']],
                    ['code' => 'rm2', 'text' => 'Do you understand the regulatory requirements in the markets you target?',
                     'options' => ['Fully', 'Partially', 'Not really'], 'risk' => 'high'],
                    ['code' => 'sec_og', 'text' => 'Are local-content ownership rules for this market clear to you?',
                     'options' => ['Yes, fully clear', 'Partially clear', 'Not clear at all'], 'sectors' => ['oil_gas'], 'risk' => 'critical'],
                    ['code' => 'sec_ag', 'text' => 'Have you mapped traceability and compliance requirements for this market?',
                     'options' => ['Yes, mapped', 'Partially', 'Not yet'], 'sectors' => ['agritech'], 'risk' => 'high'],
                    ['code' => 'sec_ft', 'text' => 'Are the licensing requirements for this market clear to you?',
                     'options' => ['Yes, fully clear', 'Partially clear', 'Not clear at all'], 'sectors' => ['fintech'], 'risk' => 'critical'],
                ]],
            ],
        ],

        /* ------------------------------------------------------------ */
        'HC' => [
            'name' => 'Human Capital & Organisational Capacity',
            'short' => 'Human Capital',
            'description' => 'Workforce capability, performance management, productivity, culture and talent development.',
            'weight' => 1,
            'ai_instructions' => 'Link people findings to execution capacity: who holds critical knowledge, whether performance is measured, and whether the organisation can absorb growth. Avoid generic HR advice; tie each recommendation to a business outcome.',
            'criteria' => [
                'HC1' => ['name' => 'Workforce capabilities', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'hc_1', 'text' => 'Do you have the skills in-house to deliver the next 12 months of plans?',
                     'options' => ['Yes', 'Some gaps', 'Significant gaps']],
                    ['code' => 'hc_2', 'text' => 'Is critical knowledge held by one person with no backup?',
                     'options' => ['No, it is shared', 'In a few areas', 'Yes, in several areas'], 'risk' => 'high'],
                ]],
                'HC2' => ['name' => 'Performance management', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'hc_3', 'text' => 'Does every role have written objectives or KPIs reviewed at least twice a year?',
                     'options' => ['Yes', 'Some roles', 'No'], 'skip_stages' => ['solo']],
                ]],
                'HC3' => ['name' => 'Staff productivity', 'programme' => 'performance', 'weight' => 1, 'questions' => [
                    ['code' => 'hc_4', 'text' => 'Can you tell which teams or people are most productive from data rather than impression?',
                     'options' => ['Yes', 'Partly', 'No'], 'skip_stages' => ['solo']],
                ]],
                'HC4' => ['name' => 'Organisational culture', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'hc_5', 'text' => 'Do staff raise problems early, without fear of blame?',
                     'options' => ['Usually', 'Sometimes', 'Rarely'], 'skip_stages' => ['solo']],
                    ['code' => 'hc_6', 'text' => 'How has staff turnover been over the last 12 months?',
                     'options' => ['Low and expected', 'Higher than we would like', 'High / losing key people'], 'skip_stages' => ['solo'], 'risk' => 'high'],
                    ['code' => 'hc_f1', 'text' => 'Do you understand why people leave (exit conversations, surveys)?',
                     'options' => ['Yes', 'Roughly', 'No'], 'show_if' => ['q' => 'hc_6', 'min' => 1]],
                ]],
                'HC5' => ['name' => 'Talent development', 'programme' => 'strategy', 'weight' => 1, 'questions' => [
                    ['code' => 'hc_7', 'text' => 'Is there a training or development budget and plan?',
                     'options' => ['Yes, planned and used', 'Ad hoc', 'No']],
                ]],
            ],
        ],
    ],

    /*
    | Cross-area observations. Visible only once two areas are read together.
    | `when` conditions (all must hold):
    |   module_severity:    {MODULE: [levels]}
    |   criterion_severity: {CRITERION: [levels]}
    |   symptom_any:        [symptom ids]
    |   stage:              [stages]
    |   min_weak_modules:   integer (modules with Medium or High severity)
    | Fallback insights only appear when fewer than `insight_limit` specific ones fire.
    */
    'insight_limit' => 3,
    'insights' => [
        ['id' => 'fc_ir', 'when' => ['criterion_severity' => ['FH1' => ['Medium', 'High'], 'FH5' => ['Medium', 'High']]],
         'title' => 'You are preparing to raise money on numbers you have told us you don\'t fully trust',
         'body' => 'Funders do not decline businesses for being small. They decline them for being unverifiable. Every weakness in the monthly reporting becomes a question in due diligence, and questions you cannot answer quickly get read as risk. The capital work is not blocked by appetite — it is blocked by the records.'],
        ['id' => 'fd_fc', 'when' => ['module_severity' => ['SG' => ['Medium', 'High']], 'criterion_severity' => ['FH1' => ['Medium', 'High']]],
         'title' => 'The finance gap and the founder gap are the same gap',
         'body' => 'When there is no finance function, the founder becomes it. That is why the numbers arrive late and why stepping away is impossible — both trace back to one person holding work that should sit in a system. Fixing the reporting is what frees the calendar, not the other way round.'],
        ['id' => 'rm_fc', 'when' => ['module_severity' => ['MG' => ['Medium', 'High']], 'criterion_severity' => ['FH3' => ['High']]],
         'title' => 'You are planning to expand without the cost visibility to know whether it worked',
         'body' => 'A new market either pays for itself or quietly consumes the margin from the old one. Without profitability by line, both look identical on the bank statement for a long time — often a year or more. Expansion multiplies whatever measurement problem already exists.'],
        ['id' => 'ir_fd', 'when' => ['module_severity' => ['SG' => ['High']], 'criterion_severity' => ['FH5' => ['Medium', 'High']]],
         'title' => 'An investor is buying a business that runs without you',
         'body' => 'Founder dependency is priced. If the business stalls when you step away, that is not a culture observation — it is a valuation discount and, for some funders, a decline. Governance work done before a raise usually pays for itself several times over in terms.'],
        ['id' => 'fd_rm', 'when' => ['module_severity' => ['SG' => ['Medium', 'High'], 'MG' => ['Medium', 'High']]],
         'title' => 'A new market multiplies the decisions that already come to you',
         'body' => 'Every decision that currently needs you will need you again in the new market, at a distance, in a context you know less well. Expansion is the point at which founder dependency stops being uncomfortable and starts being the binding constraint on growth.'],
        ['id' => 'mature_fd', 'when' => ['stage' => ['mature'], 'module_severity' => ['SG' => ['Medium', 'High']]],
         'title' => 'Succession is a documentation problem before it is a people problem',
         'body' => 'In an established business the question is rarely who could lead. It is whether what they would need to know exists anywhere outside your head. That gap is invisible until the moment it matters, and by then it cannot be closed quickly.'],
        ['id' => 'compliance', 'when' => ['symptom_any' => ['s6']],
         'title' => 'A tax or compliance surprise is a reporting symptom, not bad luck',
         'body' => 'Assessments and penalties almost never arrive without warning in the underlying records. They arrive without warning to leadership, which is a different problem — and a solvable one. Where it has happened once, the conditions for it usually remain in place.'],
        ['id' => 'docs', 'when' => ['symptom_any' => ['s11']],
         'title' => 'The documents funders asked for are ordinary month-end outputs',
         'body' => 'What you were asked for is not exotic. It is what a functioning monthly close produces as a by-product. That is why this reads as a reporting gap rather than a fundraising one, and why the fix is durable rather than a one-off scramble.'],
        ['id' => 'people_ops', 'when' => ['module_severity' => ['HC' => ['Medium', 'High'], 'OE' => ['Medium', 'High']]],
         'title' => 'Process gaps and people gaps are feeding each other',
         'body' => 'When processes live in people\'s heads, every departure takes a piece of the operation with it, and every new hire learns by trial and error. Documenting how work is done is the cheapest retention and onboarding investment available.'],
        ['id' => 'all_four', 'when' => ['min_weak_modules' => 4],
         'title' => 'Sequence will matter more than effort',
         'body' => 'Almost every area came up, which is common in a business that has grown faster than its systems. The risk now is not inaction — it is starting everywhere at once and finishing nothing. The order below is deliberate: each phase makes the next one cheaper and faster.'],
        ['id' => 'cash', 'when' => ['symptom_any' => ['s8']],
         'title' => 'Cash tight on healthy sales is a timing problem, not a sales problem',
         'body' => 'Profitable businesses run out of cash routinely. It happens in the gap between when money is committed and when it arrives, and it is only visible if someone is looking forward rather than back. Selling harder rarely fixes it, and sometimes makes it worse.'],
        ['id' => 'fd_only', 'fallback' => true, 'when' => ['module_severity' => ['SG' => ['Medium', 'High']]],
         'title' => 'The business depends on you in ways that are hard to see from inside it',
         'body' => 'Founder dependency rarely announces itself. It shows up as a full calendar, a team that checks before acting, and decisions that quietly wait. None of that looks like a problem day to day — it looks like being busy. It becomes visible at exactly the wrong moments: illness, travel, a funder\'s question about succession, or the point where growth needs you in two places.'],
        ['id' => 'fc_only', 'fallback' => true, 'when' => ['criterion_severity' => ['FH1' => ['Medium', 'High']]],
         'title' => 'You are making decisions on numbers that arrive late or cannot be trusted',
         'body' => 'Every pricing call, hiring call and spending call rests on the same financial picture. When that picture is late, incomplete, or assembled from memory, the decisions are not wrong so much as unsupported — and the cost never appears as a single event. It shows up as margin that erodes quietly and surprises that arrive fully formed.'],
        ['id' => 'ir_only', 'fallback' => true, 'when' => ['criterion_severity' => ['FH5' => ['Medium', 'High']]],
         'title' => 'Funders decline what they cannot verify, not what is small',
         'body' => 'Capital conversations are won or lost on how quickly a business can answer questions with evidence. What looks like a fundraising problem is usually a preparation problem: the records, the story and the numbers have not been assembled into something a funder can check quickly. That is fixable, and it is fixable before the next conversation.'],
        ['id' => 'rm_only', 'fallback' => true, 'when' => ['module_severity' => ['MG' => ['Medium', 'High']]],
         'title' => 'Most failed market entries fail on preparation, not on the market',
         'body' => 'New markets punish assumption. Regulation, partner quality and real demand are all knowable before committing capital, and each one is expensive to discover afterwards. The question is rarely whether the opportunity is real — it is whether the groundwork has been done to a standard that makes the risk worth taking.'],
        ['id' => 'oe_only', 'fallback' => true, 'when' => ['module_severity' => ['OE' => ['Medium', 'High']]],
         'title' => 'Effort is compensating for missing process',
         'body' => 'Operations that run on goodwill and memory work until volume rises or a key person is away. The hidden cost is rework, delay and controls that exist only in principle. Making the work visible — a few documented processes and a handful of measures — usually releases capacity before it requires investment.'],
        ['id' => 'hc_only', 'fallback' => true, 'when' => ['module_severity' => ['HC' => ['Medium', 'High']]],
         'title' => 'Capacity, not ambition, is the constraint',
         'body' => 'Plans fail quietly when the people delivering them lack clear objectives, measures or backup. Strengthening how performance is set and reviewed is less visible than hiring, but it is what lets the existing team carry more without burning out.'],
    ],
];
