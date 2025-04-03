<?php
/**
 * Plugin Name: Tutor LMS Extension
 * Description: Extends Tutor LMS functionalities with quiz expiration and group features.
 * Version: 1.0
 * Author: Victor Etiefe
 * License: GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-tutor-quiz-expiration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-tutor-groups.php';

// display the quiz end date on th frontend
function tutor_quiz_expiration_message() {
    global $post;
    
    if (!$post || $post->post_type !== 'tutor_quiz') {
        return;
    }

    // Get the expiration date from post meta
    $expiration_date = get_post_meta($post->ID, '_tutor_quiz_expiration', true);

    if ($expiration_date) {
        $current_time = current_time('Y-m-d H:i');
        $timestamp = strtotime($expiration_date) * 1000; // Convert to JavaScript timestamp
        $formatted_date = date("F j, Y, g:i A", strtotime($expiration_date)); // Example: April 3, 2025, 2:05 AM

        if ($current_time >= $expiration_date) {
            // Quiz has expired → Show warning inside the quiz body
            add_action('tutor_single_quiz/body', function() use ($formatted_date) {
                echo '<div class="tutor-alert tutor-alert-warning">⚠️ This quiz expired on <strong>' . esc_html($formatted_date) . '</strong> and can no longer be attempted.</div>';
            });
        } else {
            // Quiz is still active → Show countdown at the top
            add_action('tutor_quiz/body/before', function() use ($timestamp, $formatted_date) {
                ?>
                <div class="tutor-alert tutor-alert-info">
                    ℹ️ This quiz will expire in: <span id="quiz-expiration-timer"></span><br>
                    📅 Expiration Date: <strong><?php echo esc_html($formatted_date); ?></strong>
                </div>
                <script>
                    function startQuizCountdown(expirationTime) {
                        function updateCountdown() {
                            const now = new Date().getTime();
                            const timeLeft = expirationTime - now;
                            
                            if (timeLeft <= 0) {
                                document.getElementById("quiz-expiration-timer").innerHTML = "EXPIRED";
                                return;
                            }

                            const hours = Math.floor((timeLeft / (1000 * 60 * 60)) % 24);
                            const minutes = Math.floor((timeLeft / (1000 * 60)) % 60);
                            const seconds = Math.floor((timeLeft / 1000) % 60);

                            document.getElementById("quiz-expiration-timer").innerHTML =
                                hours + "h " + minutes + "m " + seconds + "s ";

                            setTimeout(updateCountdown, 1000);
                        }
                        updateCountdown();
                    }

                    document.addEventListener("DOMContentLoaded", function() {
                        startQuizCountdown(<?php echo esc_js($timestamp); ?>);
                    });
                </script>
                <?php
            });
        }
    }
}

// Run this function on WordPress init
add_action('wp', 'tutor_quiz_expiration_message');



