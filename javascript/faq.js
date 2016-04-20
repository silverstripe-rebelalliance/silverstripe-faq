/**
 * FAQ Module javascript for the frontend.
 */
;(function ($) {
    $(document).ready(function () {
        // assuming only one form in DOM
        var $form = $('.faq__rating'),
            $usefuls = $form.find('input[name="Useful"]'),
            $comment = $form.find('#Comment'),
            $actions = $form.find('.Actions');

        if ($comment.find(':input').val()) {
            $comment.hide();
        }
        $actions.hide();
        $form.on('change', function () {
            var $useful = $usefuls.filter(':checked');

            if ($useful.val() === 'Y') {
                $comment.find(':input').val('');
                $comment.hide();
            }
            else {
                $comment.show();
            }
            $actions.show();
        });
    });
}(jQuery));
