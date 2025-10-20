<?php
/**
 * AJAX endpoint for handling star ratings
 */
function handle_star_rating_ajax() {
    // Check if it's an AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        http_response_code(403);
        exit('Forbidden');
    }
    
    // Validate input
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
    
    if (!$itemId || !$rating || $rating < 0 || $rating > 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid rating data'
        ]);
        exit;
    }
    
    // Here you would save to your database
    // Example implementation:
    try {
        // Save rating to database
        $success = save_rating_to_database($itemId, $rating);
        
        if ($success) {
            // Get updated average rating
            $averageRating = get_average_rating($itemId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rating saved successfully',
                'average_rating' => $averageRating,
                'new_rating' => $rating
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save rating'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// Example database functions (implement according to your DB structure)
function save_rating_to_database($itemId, $rating) {
    // Your database implementation here
    // This is just a placeholder
    return true;
}

function get_average_rating($itemId) {
    // Your database implementation here
    // This is just a placeholder
    return 4.5;
}
?>
