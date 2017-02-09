
@if ($options["useCaptcha"])
<script src='https://www.google.com/recaptcha/api.js{{ ($options["noCaptchaLanguage"] != "") ? "?hl=" . $options["noCaptchaLanguage"] : "" }}'></script>
@endif

<script>

    setTimeout(function(){

        if (!window.FormData) return;

        //if no "accept" is set on file uploads - restrict based on form settings
        var files = document.querySelectorAll("input[type=file]");
        for (var ix =0; ix < files.length; ix++) {
            if (!files[ix].hasAttribute("accept")) {
                files[ix].setAttribute("accept", "{{ $options["allowedExtensions"] }}");
            }
        }

        var form        = document.getElementById("{{ $options['id'] }}"),
            msgsuccess  = form.getElementsByClassName("{{ isset($options["successElementClassName"]) ? $options["successElementClassName"] : "form-message-success" }}").item(0),

            msgfail     = form.getElementsByClassName("{{ isset($options["failElementClassName"]) ? $options["failElementClassName"] : "form-message-fail" }}").item(0),

            onSuccess = {{ isset($options["successCallback"]) ? $options["successCallback"] : "false" }},
            onFail = {{ isset($options["failCallback"]) ? $options["failCallback"] : "false" }},
            onSubmitStarted = {{ isset($options["submitStartedCallback"]) ? $options["submitStartedCallback"] : "false" }},
            onSubmitFinished = {{ isset($options["submitFinishedCallback"]) ? $options["submitFinishedCallback"] : "false" }},


            checkFileSizes = function() {

@if ($options["maxFileSize"] && $options["maxFileSize"] > 0)                

                if (!window.FileReader) {                   
                   return true;
                }   

                for (var ix = 0; ix < files.length; ix++) {
                    if (files[ix].files) {
                        for (yx = 0; yx < files[ix].files.length; yx++) {
                            if (files[ix].files[yx].size/1024/1024 > {{ $options["maxFileSize"] }}) {
                                return false;
                            }
                        }
                    }
                }
@endif
                return true;

            },

            disableForm = function(status) {
                for(var i=0, max=form.elements.length;i<max;i++) form.elements[i].disabled = status;
            },
            success     = function() {

                if (onSubmitFinished) {
                    onSubmitFinished();
                }

                if (msgsuccess) {
                    msgsuccess.style.display = 'block';
                }

                if (onSuccess) {
                    onSuccess();
                }

                if (!msgsuccess && !onSuccess) {
                    alert("@lang('Form submission was successfull.')");
                }

                disableForm(false);
                grecaptcha.reset();
            },
            fail        = function(){
                if (onSubmitFinished) {
                    onSubmitFinished();
                }

                if (msgfail) {
                    msgfail.style.display = 'block';
                } 

                if (onFail) {
                    onFail();
                }

                if (!msgfail && !onFail) {
                    alert("@lang('Form submission failed.')");
                }

                disableForm(false);
            };

@if ($options["useCaptcha"])
        var captcha = document.getElementById("{{ $options["captchaId"] }}");
        var captchaNode = document.createElement("div");
        captchaNode.setAttribute("class", "g-recaptcha");
        captchaNode.setAttribute("data-sitekey", "{{ $options["captchaSiteKey"] }}");
        captcha.appendChild(captchaNode);
@endif


        if (msgsuccess) msgsuccess.style.display = "none";
        if (msgfail)    msgfail.style.display = "none";

        form.addEventListener("submit", function(e) {

            e.preventDefault();

 @if ($options["useCaptcha"])
            if (document.querySelectorAll('textarea[name="g-recaptcha-response"]')[0].value == "") {
                alert("{{ ($options["noCaptchaRequired"] != "") ?  str_replace('"', '\"', $options["noCaptchaRequired"]) : "Please check 'I am not a robot'." }}");
                return false;
            }
 @endif           

            if (msgsuccess) msgsuccess.style.display = "none";
            if (msgfail)    msgfail.style.display = "none";

            if (!checkFileSizes()) {
                alert("{{ (isset($options["maxFileSizeMessage"]) && $options["maxFileSizeMessage"] != "") ? str_replace('"', '\"' , $options["maxFileSizeMessage"]) : "You have exceeded the maximum file size of " . $options["maxFileSize"] . "MB."  }}");
                return false;
            }

            if (onSubmitStarted) {
                onSubmitStarted();
            }

            var xhr = new XMLHttpRequest(), data = new FormData(form);

            xhr.onload = function() {

                if (this.status == 200 && this.responseText!='false') {
                    success();
                    form.reset();
                } else {
                    fail();
                }
            };

            disableForm(true);

            xhr.open('POST', "@route('/api/advancedforms/submit/'.$name)", true);
            xhr.send(data);

        }, false);

    }, 100);

</script>

<form id="{{ $options["id"] }}" name="{{ $name }}" class="{{ $options["class"] }}" action="@route('/api/advancedforms/submit/'.$name)" method="post" onsubmit="return false;" enctype="multipart/form-data">
<input type="hidden" name="__csrf" value="{{ $options["csrf"] }}">
<input type="hidden" name="__fromEmailInputName" value="{{ $options["fromEmailInputName"] }}">
<input type="hidden" name="__fromNameInputName" value="{{ $options["fromNameInputName"] }}">