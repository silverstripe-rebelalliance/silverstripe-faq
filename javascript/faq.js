/**
 * FAQ Module javascript for the frontend.
 */
;(function ($) {
    $(document).ready(function () {
        // assuming only one form in DOM
        var $form = $('.faq__rating'),
            $usefuls = $form.find('input[name="Useful"]'),
            $comment = $form.find('#Comment'),
            $actions = $form.find('.Actions')
            existingComment = null;

        $form.on('change', function () {
            var $useful = $usefuls.filter(':checked');

            if ($useful.val() === 'Y') {
                existingComment = $comment.find(':input').val();
                $comment.find(':input').val('');
                $comment.hide();
            }
            else {
                if (existingComment) {
                    $comment.find(':input').val(existingComment);
                }
                $comment.show();
            }
        }).change();
    });
}(jQuery));
