jQuery(document).ready(function($) {
    $('.rtc-buttons').on('click', 'button', function(e) {
        e.preventDefault();

        var button = $(this);
        var parent = button.closest('.rtc-buttons');
        var commentId = parent.data('comment-id');
        var action = button.hasClass('rtc-approve') ? 'rtc_approve_comment' : 'rtc_reject_comment';

        $.ajax({
            url: RTCModeration.ajax_url,
            type: 'POST',
            data: {
                action: action,
                comment_id: commentId,
                nonce: RTCModeration.nonce
            },
            success: function(response) {
                if (response.success) {
                    parent.html('<em>' + response.data + '</em>');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('AJAX error');
            }
        });
    });
});
