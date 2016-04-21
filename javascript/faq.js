/**
 * FAQ Module javascript for the frontend.
 */
;(function ($) {
    $(document).ready(function () {
        // assuming only one form in DOM
        var $form = $('.faq__rating'),
            $usefuls = $form.find('input[name="Useful"]'),
            $comment = $form.find('#Comment'),
            $actions = $form.find('.action'),
            existingComment = null;

        $form.on('change', showHideComment);

        showHideComment();

        function showHideComment() {
            var $useful = $usefuls.filter(':checked');

            if ($useful.val() === 'Y') {
                existingComment = $comment.find(':input').val();
                $comment.find(':input').val('');

                $comment.hide();
                $actions.prop('disabled', false);
            }
            else if ($useful.val() === 'N') {
                if (existingComment) {
                    $comment.find(':input').val(existingComment);
                }
                $comment.show();
                $actions.prop('disabled', false);
            }
            else {
                $comment.hide();
                $actions.prop('disabled', true);
            }
        }
    });
}(jQuery));
