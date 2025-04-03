<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tutor_Quiz_Expiration {

    public function __construct() {
        // Add expiration field on quiz edit screen
        add_action('tutor_quiz_edit_modal_settings_tab_after_max_allowed_questions', [$this, 'add_quiz_expiration_field']);
        
        // Save the expiration date when the quiz is updated (hooking into save_post_tutor_quiz)
        add_action('save_post_tutor_quiz', [$this, 'save_quiz_expiration_field'], 10, 3);

            // Hook this function into the modal view
        add_action('tutor_quiz_edit_modal_settings_tab_after_max_allowed_questions', [$this, 'display_expiration_in_modal']);
    }

    // Display expiration field on quiz edit screen
    public function add_quiz_expiration_field($quiz) {
        $expiration_date = get_post_meta($quiz->ID, '_tutor_quiz_expiration', true);
        ?>
        <div class="tutor-form-group">
            <label for="tutor_quiz_expiration">Quiz Expiration Date</label>
            <input type="datetime-local" id="tutor_quiz_expiration" name="tutor_quiz_expiration" 
                   value="<?php echo esc_attr($expiration_date); ?>">
        </div>
        <?php
    }

    // Save the expiration date when quiz is updated
    public function save_quiz_expiration_field($quiz_id, $post, $update) {
        // Check if the expiration date is set in the form submission
        if (isset($_POST['tutor_quiz_expiration'])) {
            $expiration_date = sanitize_text_field($_POST['tutor_quiz_expiration']);

            // If expiration date is provided, save it as post meta
            if (!empty($expiration_date)) {
                update_post_meta($quiz_id, '_tutor_quiz_expiration', $expiration_date);
                error_log('Expiration Date Saved: ' . $expiration_date);
            } else {
                // If expiration date is empty, delete the meta
                delete_post_meta($quiz_id, '_tutor_quiz_expiration');
                error_log('Expiration Date Deleted');
            }
        } else {
            error_log('Expiration Date Not Set');
        }
    }
    // Display expiration date and expired message
    public function display_expiration_in_modal($quiz) {
        $expiration_date = get_post_meta($quiz->ID, '_tutor_quiz_expiration', true);

        if ($expiration_date) {
            // Check if the expiration date has passed
            $current_time = current_time('Y-m-d H:i:s'); // Get current time in WordPress format
            $is_expired = strtotime($expiration_date) < strtotime($current_time);

            // Format the expiration date for display
            $formatted_date = date('F j, Y, g:i a', strtotime($expiration_date)); // Example format: April 3, 2025, 2:05 am

            // Display the expiration date
            echo '<div class="tutor-form-group">';
            echo '<label for="tutor_quiz_expiration_display">Quiz Expiration Date</label>';
            echo '<input type="text" id="tutor_quiz_expiration_display" value="' . esc_attr($formatted_date) . '" disabled>';
            echo '</div>';

            // Display expired message if the quiz has expired
            if ($is_expired) {
                echo '<div class="quiz-expired-message" style="color: red; font-weight: bold;">This quiz has expired.</div>';
            }
        } else {
            echo '<div class="quiz-expiration-date">No expiration date set.</div>';
        }
    }

    

}

// Initialize the class
new Tutor_Quiz_Expiration();
