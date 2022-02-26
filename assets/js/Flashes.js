export default class Flashes {

    static init() {
        $(document).click(function() {
            $(".app-flash-box").fadeTo(200, 0.05, function () {
                $(this).remove();
            });
        });
        $(".app-flash-box").click(function(e) {
            e.stopPropagation();
            return false;
        });
    }

}