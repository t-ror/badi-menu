export default class PreventDoubleSubmit {

    static init() {
        $('form.app_prevent-double-submit').submit(function(){
            $(this).find(':submit').attr('disabled','disabled');
        });
    }

}