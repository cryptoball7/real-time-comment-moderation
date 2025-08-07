<?php
/*
Plugin Name: Real-Time Comment Moderation
Description: Adds AJAX-powered front-end comment approval/rejection for moderators.
Version: 1.0
Author: Cryptoball cryptoball7@gmail.com
*/

if (!defined('ABSPATH')) exit;

class RealTimeCommentModeration {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('comment_text', [$this, 'add_moderation_buttons'], 10, 2);

        // AJAX handlers
        add_action('wp_ajax_rtc_approve_comment', [$this, 'approve_comment']);
        add_action('wp_ajax_rtc_reject_comment', [$this, 'reject_comment']);
    }

    public function enqueue_scripts() {
        if (!current_user_can('moderate_comments')) return;

        wp_enqueue_script('rtc-moderation', plugin_dir_url(__FILE__) . 'js/moderation.js', ['jquery'], '1.0', true);

        wp_localize_script('rtc-moderation', 'RTCModeration', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rtc_comment_action')
        ]);
    }

    public function add_moderation_buttons($comment_text, $comment) {
        if (!current_user_can('moderate_comments') || $comment->comment_approved == 1) {
            return $comment_text;
        }

        $comment_id = $comment->comment_ID;
        $buttons = sprintf(
            '<div class="rtc-buttons" data-comment-id="%d">
                <button class="rtc-approve">Approve</button>
                <button class="rtc-reject">Reject</button>
             </div>',
            esc_attr($comment_id)
        );

        return $comment_text . $buttons;
    }

    public function approve_comment() {
        $this->handle_action('approve');
    }

    public function reject_comment() {
        $this->handle_action('reject');
    }

    private function handle_action($action) {
        check_ajax_referer('rtc_comment_action', 'nonce');

        if (!current_user_can('moderate_comments')) {
            wp_send_json_error('Unauthorized');
        }

        $comment_id = intval($_POST['comment_id']);
        if (get_comment($comment_id) === null) {
            wp_send_json_error('Invalid comment');
        }

        if ($action === 'approve') {
            wp_set_comment_status($comment_id, 'approve');
            wp_send_json_success('Comment approved');
        } elseif ($action === 'reject') {
            wp_set_comment_status($comment_id, 'spam');
            wp_send_json_success('Comment rejected');
        }

        wp_send_json_error('Unknown action');
    }
}

new RealTimeCommentModeration();
