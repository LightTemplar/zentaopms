$(document).ready(function()
{
    var originalEncrypted = false;
    $('#originalPassword').change(function(){originalEncrypted = false});

    var password1Encrypted = false
    var password2Encrypted = false
    $('#password1').change(function(){password1Encrypted = false});
    $('#password2').change(function(){password2Encrypted = false});

    $('#submit').click(function()
    {
        var password         = $('input#originalPassword').val().trim();
        var password1        = $('input#password1').val().trim();
        var passwordStrength = computePasswordStrength(password1);
        var rand             = $('input#verifyRand').val();
        if(!password1Encrypted) $("#passwordLength").val(password1.length);

        if($("form input[name=passwordStrength]").length == 0) $('#submit').after("<input type='hidden' name='passwordStrength' value='0' />");
        $("form input[name=passwordStrength]").val(passwordStrength);

        if(!originalEncrypted && password) $('input#originalPassword').val(md5(md5(password) + rand));
        originalEncrypted = true;

        var password1 = $('#password1').val().trim();
        var password2 = $('#password2').val().trim();
        if(password1 && !password1Encrypted) $('#password1').val(md5(password1) + rand);
        if(password2 && !password2Encrypted) $('#password2').val(md5(password2) + rand);
        password1Encrypted = true;
        password2Encrypted = true;
    });
});
