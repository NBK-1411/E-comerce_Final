<?php
/**
 * AI Chatbot Action - Enhanced with Database Integration and User Context
 * Returns JSON response with AI-generated answer
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/activity_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($data['message']) ? trim($data['message']) : '';
$history = isset($data['history']) ? $data['history'] : [];

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit();
}

// Check if user is logged in
$user_id = is_logged_in() ? $_SESSION['customer_id'] : null;
$user_name = is_logged_in() ? ($_SESSION['customer_name'] ?? 'User') : null;

// Detect intent and get relevant data
$intent = detectIntent($userMessage);
$relevantData = getRelevantData($intent, $userMessage, $user_id);

// Check if OpenAI API key is configured
if (!defined('OPENAI_API_KEY') || empty(constant('OPENAI_API_KEY'))) {
    // Enhanced fallback with database queries
    $response = generateEnhancedFallbackResponse($userMessage, $intent, $relevantData, $user_id);
    echo json_encode(['success' => true, 'response' => $response]);
    exit();
}

// Prepare comprehensive context
$context = getEnhancedPlatformContext($user_id, $relevantData);

// Build conversation messages for OpenAI
$messages = [
    [
        'role' => 'system',
        'content' => "You are a helpful AI assistant for Go Outside, a venue booking platform in Ghana. " .
                     "Help users find venues, understand booking processes, and navigate the platform. " .
                     "Be friendly, concise, and helpful. " .
                     "When mentioning venues or activities, use the actual data provided. " .
                     "If the user is logged in, you can reference their bookings and saved items. " .
                     "Always provide actionable information. " . $context
    ]
];

// Add conversation history
foreach ($history as $msg) {
    $messages[] = [
        'role' => $msg['role'],
        'content' => $msg['content']
    ];
}

// Add current user message
$messages[] = [
    'role' => 'user',
    'content' => $userMessage
];

// Call OpenAI API
$response = callOpenAI($messages);

if ($response) {
    // Add action links if relevant
    $response = addActionLinks($response, $intent, $relevantData);
    echo json_encode(['success' => true, 'response' => $response]);
} else {
    // Enhanced fallback if API fails
    $fallback = generateEnhancedFallbackResponse($userMessage, $intent, $relevantData, $user_id);
    echo json_encode(['success' => true, 'response' => $fallback]);
}

/**
 * Detect user intent from message
 */
function detectIntent($message) {
    $message = strtolower($message);
    
    if (preg_match('/\b(book|booking|reserve|reservation|book a|make a booking)\b/', $message)) {
        return 'booking';
    }
    if (preg_match('/\b(my|show|list|view|see).*booking/i', $message)) {
        return 'my_bookings';
    }
    if (preg_match('/\b(saved|favorite|collection|saved items|my saved)\b/', $message)) {
        return 'saved_items';
    }
    if (preg_match('/\b(find|search|look for|where can i|need|want).*venue/i', $message)) {
        return 'search_venue';
    }
    if (preg_match('/\b(find|search|look for|where can i|need|want).*activity/i', $message)) {
        return 'search_activity';
    }
    if (preg_match('/\b(price|cost|fee|how much|pricing)\b/', $message)) {
        return 'pricing';
    }
    if (preg_match('/\b(pay|payment|how to pay|payment method)\b/', $message)) {
        return 'payment';
    }
    if (preg_match('/\b(available|availability|free|open)\b/', $message)) {
        return 'availability';
    }
    if (preg_match('/\b(category|categories|type|types|what kind)\b/', $message)) {
        return 'categories';
    }
    if (preg_match('/\b(location|where|area|region|accra|ghana)\b/', $message)) {
        return 'location';
    }
    
    return 'general';
}

/**
 * Get relevant data based on intent
 */
function getRelevantData($intent, $message, $user_id) {
    $data = [];
    
    try {
        switch ($intent) {
            case 'my_bookings':
                if ($user_id) {
                    $bookings = get_user_bookings_ctr($user_id);
                    $upcoming = get_upcoming_bookings_ctr($user_id);
                    $past = get_past_bookings_ctr($user_id);
                    $data['bookings'] = $bookings;
                    $data['upcoming'] = $upcoming;
                    $data['past'] = $past;
                }
                break;
                
            case 'saved_items':
                if ($user_id) {
                    $saved = get_saved_venues_ctr($user_id);
                    $data['saved'] = $saved;
                }
                break;
                
            case 'search_venue':
            case 'categories':
                // Get venues matching keywords
                $keywords = extractKeywords($message);
                $venues = get_all_approved_venues_ctr();
                if (!empty($keywords)) {
                    $filtered = array_filter($venues, function($v) use ($keywords) {
                        $text = strtolower($v['title'] . ' ' . $v['cat_name'] . ' ' . $v['location_text']);
                        foreach ($keywords as $keyword) {
                            if (strpos($text, strtolower($keyword)) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                    $data['venues'] = array_slice($filtered, 0, 5);
                } else {
                    $data['venues'] = array_slice($venues, 0, 5);
                }
                // Get categories
                $categories = array_unique(array_column($venues, 'cat_name'));
                $data['categories'] = $categories;
                break;
                
            case 'search_activity':
                $keywords = extractKeywords($message);
                $activities = get_all_approved_activities_ctr();
                if (!empty($keywords)) {
                    $filtered = array_filter($activities, function($a) use ($keywords) {
                        $text = strtolower($a['title'] . ' ' . $a['activity_type'] . ' ' . $a['location_text']);
                        foreach ($keywords as $keyword) {
                            if (strpos($text, strtolower($keyword)) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                    $data['activities'] = array_slice($filtered, 0, 5);
                } else {
                    $data['activities'] = array_slice($activities, 0, 5);
                }
                break;
                
            case 'pricing':
                $venues = get_all_approved_venues_ctr();
                $data['pricing_examples'] = array_map(function($v) {
                    return [
                        'name' => $v['title'],
                        'min' => $v['price_min'],
                        'max' => $v['price_max']
                    ];
                }, array_slice($venues, 0, 5));
                break;
        }
    } catch (Exception $e) {
        error_log("Chatbot data fetch error: " . $e->getMessage());
    }
    
    return $data;
}

/**
 * Extract keywords from message
 */
function extractKeywords($message) {
    // Remove common words
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'i', 'want', 'need', 'find', 'search', 'look', 'for'];
    $words = preg_split('/\s+/', strtolower($message));
    return array_filter($words, function($word) use ($stopwords) {
        return strlen($word) > 2 && !in_array($word, $stopwords);
    });
}

/**
 * Get enhanced platform context with real data
 */
function getEnhancedPlatformContext($user_id, $relevantData) {
    $context = "Platform Information:\n";
    $context .= "- Users can browse and book venues and activities in Ghana\n";
    $context .= "- Venues include restaurants, clubs, event spaces, studios, and short stays\n";
    $context .= "- Activities include food tours, adventure activities, cultural experiences\n";
    $context .= "- Users can search by location, category, and price\n";
    $context .= "- Booking requires selecting date, time, and number of guests\n";
    $context .= "- Payment is processed through Paystack (card or mobile money)\n";
    $context .= "- Booking deposit is typically 30% of total cost\n";
    $context .= "- Users can save favorite venues to collections\n";
    $context .= "- Reviews are available for verified bookings\n";
    
    // Add user-specific context
    if ($user_id) {
        $context .= "\nCurrent User Context:\n";
        $context .= "- User is logged in\n";
        
        if (isset($relevantData['bookings'])) {
            $upcoming_count = count($relevantData['upcoming'] ?? []);
            $past_count = count($relevantData['past'] ?? []);
            $context .= "- User has {$upcoming_count} upcoming bookings and {$past_count} past bookings\n";
            
            if (!empty($relevantData['upcoming'])) {
                $context .= "- Upcoming bookings: ";
                $upcoming_list = array_map(function($b) {
                    return $b['venue_title'] . " on " . date('M d, Y', strtotime($b['booking_date']));
                }, array_slice($relevantData['upcoming'], 0, 3));
                $context .= implode(', ', $upcoming_list) . "\n";
            }
        }
        
        if (isset($relevantData['saved'])) {
            $saved_count = count($relevantData['saved']);
            $context .= "- User has {$saved_count} saved venues in collections\n";
        }
    } else {
        $context .= "\nUser is not logged in. Suggest they login to see their bookings and saved items.\n";
    }
    
    // Add venue/activity data
    if (isset($relevantData['venues']) && !empty($relevantData['venues'])) {
        $context .= "\nRelevant Venues:\n";
        foreach (array_slice($relevantData['venues'], 0, 5) as $venue) {
            $context .= "- {$venue['title']} ({$venue['cat_name']}) - " . 
                       "GH₵{$venue['price_min']}-{$venue['price_max']} - " . 
                       "{$venue['location_text']}\n";
        }
    }
    
    if (isset($relevantData['activities']) && !empty($relevantData['activities'])) {
        $context .= "\nRelevant Activities:\n";
        foreach (array_slice($relevantData['activities'], 0, 5) as $activity) {
            $context .= "- {$activity['title']} ({$activity['activity_type']}) - " . 
                       "GH₵{$activity['price_min']}-{$activity['price_max']} - " . 
                       "{$activity['location_text']}\n";
        }
    }
    
    if (isset($relevantData['categories'])) {
        $context .= "\nAvailable Categories: " . implode(', ', $relevantData['categories']) . "\n";
    }
    
    // Get platform stats
    try {
        $all_venues = get_all_approved_venues_ctr();
        $all_activities = get_all_approved_activities_ctr();
        $context .= "\nPlatform Statistics:\n";
        $context .= "- Total approved venues: " . count($all_venues) . "\n";
        $context .= "- Total approved activities: " . count($all_activities) . "\n";
    } catch (Exception $e) {
        // Ignore
    }
    
    return $context;
}

/**
 * Add action links to response
 */
function addActionLinks($response, $intent, $relevantData) {
    $links = [];
    
    switch ($intent) {
        case 'my_bookings':
            if (isset($relevantData['bookings']) && !empty($relevantData['bookings'])) {
                $links[] = "View all bookings: <a href='/Event-Management-Website/public/profile.php?tab=bookings' style='color: var(--accent);'>My Bookings</a>";
            }
            break;
        case 'saved_items':
            if (isset($relevantData['saved']) && !empty($relevantData['saved'])) {
                $links[] = "View collections: <a href='/Event-Management-Website/public/profile.php?tab=collections' style='color: var(--accent);'>My Collections</a>";
            }
            break;
        case 'search_venue':
            if (isset($relevantData['venues']) && !empty($relevantData['venues'])) {
                $venue = $relevantData['venues'][0];
                $links[] = "View venue: <a href='/Event-Management-Website/public/venue_detail.php?id={$venue['venue_id']}' style='color: var(--accent);'>{$venue['title']}</a>";
                $links[] = "Browse all venues: <a href='/Event-Management-Website/public/search.php' style='color: var(--accent);'>Search Venues</a>";
            }
            break;
        case 'search_activity':
            if (isset($relevantData['activities']) && !empty($relevantData['activities'])) {
                $links[] = "Browse activities: <a href='/Event-Management-Website/public/search.php?type=activities' style='color: var(--accent);'>Search Activities</a>";
            }
            break;
    }
    
    if (!empty($links)) {
        $response .= "\n\n" . implode("\n", $links);
    }
    
    return $response;
}

/**
 * Call OpenAI API
 */
function callOpenAI($messages) {
    $apiKey = defined('OPENAI_API_KEY') ? constant('OPENAI_API_KEY') : '';
    if (empty($apiKey)) {
        return false;
    }
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 400,
        'temperature' => 0.7
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("OpenAI API error: " . $error);
        return false;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return trim($result['choices'][0]['message']['content']);
    }
    
    return false;
}

/**
 * Generate enhanced fallback response with database data
 */
function generateEnhancedFallbackResponse($message, $intent, $relevantData, $user_id) {
    $message_lower = strtolower($message);
    
    // Greetings
    if (preg_match('/\b(hi|hello|hey|greetings)\b/', $message_lower)) {
        $greeting = "Hello! I'm here to help you discover amazing venues and activities in Ghana.";
        if ($user_id) {
            $greeting .= " I can help you with your bookings, saved venues, and finding new places.";
        }
        return $greeting . " What would you like to know?";
    }
    
    // My bookings
    if ($intent === 'my_bookings' && $user_id) {
        if (isset($relevantData['upcoming']) && !empty($relevantData['upcoming'])) {
            $upcoming = $relevantData['upcoming'];
            $response = "You have " . count($upcoming) . " upcoming booking(s):\n";
            foreach (array_slice($upcoming, 0, 3) as $booking) {
                $date = date('M d, Y', strtotime($booking['booking_date']));
                $time = date('g:i A', strtotime($booking['start_time']));
                $response .= "• {$booking['venue_title']} on {$date} at {$time}\n";
            }
            $response .= "\n<a href='/Event-Management-Website/public/profile.php?tab=bookings' style='color: var(--accent);'>View all bookings</a>";
            return $response;
        } else {
            return "You don't have any upcoming bookings. Would you like to search for venues to book?";
        }
    }
    
    // Saved items
    if ($intent === 'saved_items' && $user_id) {
        if (isset($relevantData['saved']) && !empty($relevantData['saved'])) {
            $saved = $relevantData['saved'];
            $response = "You have " . count($saved) . " saved venue(s) in your collections:\n";
            foreach (array_slice($saved, 0, 5) as $item) {
                $response .= "• {$item['title']} ({$item['cat_name']})\n";
            }
            $response .= "\n<a href='/Event-Management-Website/public/profile.php?tab=collections' style='color: var(--accent);'>View collections</a>";
            return $response;
        } else {
            return "You don't have any saved venues yet. Browse venues and click the heart icon to save them to your collections!";
        }
    }
    
    // Venue search
    if ($intent === 'search_venue' && isset($relevantData['venues']) && !empty($relevantData['venues'])) {
        $venues = $relevantData['venues'];
        $response = "I found " . count($venues) . " venue(s) that might interest you:\n";
        foreach ($venues as $venue) {
            $response .= "• {$venue['title']} ({$venue['cat_name']}) - GH₵{$venue['price_min']}-{$venue['price_max']} - {$venue['location_text']}\n";
        }
        $response .= "\n<a href='/Event-Management-Website/public/search.php' style='color: var(--accent);'>Browse all venues</a>";
        return $response;
    }
    
    // Activity search
    if ($intent === 'search_activity' && isset($relevantData['activities']) && !empty($relevantData['activities'])) {
        $activities = $relevantData['activities'];
        $response = "I found " . count($activities) . " activity/activities:\n";
        foreach ($activities as $activity) {
            $response .= "• {$activity['title']} ({$activity['activity_type']}) - GH₵{$activity['price_min']}-{$activity['price_max']}\n";
        }
        $response .= "\n<a href='/Event-Management-Website/public/search.php?type=activities' style='color: var(--accent);'>Browse all activities</a>";
        return $response;
    }
    
    // Categories
    if ($intent === 'categories' && isset($relevantData['categories'])) {
        $categories = $relevantData['categories'];
        return "We have these venue categories: " . implode(', ', $categories) . ". Which one interests you?";
    }
    
    // Booking questions
    if (preg_match('/\b(book|booking|reserve|reservation)\b/', $message_lower)) {
        return "To book a venue: 1) Search for venues, 2) Select a venue you like, 3) Choose your date and time, 4) Complete payment through Paystack. <a href='/Event-Management-Website/public/search.php' style='color: var(--accent);'>Start searching</a>";
    }
    
    // Payment questions
    if (preg_match('/\b(pay|payment|price|cost|fee)\b/', $message_lower)) {
        $response = "We accept payments through Paystack (card or mobile money). Booking deposit is typically 30% of total cost.";
        if (isset($relevantData['pricing_examples']) && !empty($relevantData['pricing_examples'])) {
            $response .= "\n\nExample pricing:\n";
            foreach (array_slice($relevantData['pricing_examples'], 0, 3) as $example) {
                $response .= "• {$example['name']}: GH₵{$example['min']}-{$example['max']}\n";
            }
        }
        return $response;
    }
    
    // Venue search (general)
    if (preg_match('/\b(find|search|venue|place|location)\b/', $message_lower)) {
        return "You can search for venues by name, location, or category. <a href='/Event-Management-Website/public/search.php' style='color: var(--accent);'>Browse venues</a> or use the search bar on the homepage.";
    }
    
    // Help
    if (preg_match('/\b(help|support|assist|how|what|where)\b/', $message_lower)) {
        $help = "I can help you with:\n";
        $help .= "• Finding venues and activities\n";
        $help .= "• Understanding the booking process\n";
        $help .= "• Payment information\n";
        if ($user_id) {
            $help .= "• Your bookings and saved items\n";
        }
        $help .= "\nWhat would you like to know?";
        return $help;
    }
    
    // Default response
    return "I understand you're asking about: " . htmlspecialchars($message) . ". I can help you find venues, understand bookings, and navigate the platform. What specific question do you have?";
}

?>
