$(document).ready(function(){
    function addError(msg) {
        $('#server-messages').html('');
        $('#server-errors').html('<li>'+msg+'</li>').show();
    }
    function addMessage(msg) {
        $('#server-errors').html('');
        $('#server-messages').html('<li>'+msg+'</li>').show();
    }
    
    $('#validate-btn').on('click', function(e){
        $('#server-errors', '#server-messages').hide()
        if(!$('#url-input').val()) {
            addError('A valid domain is required.');
        } else {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            url=window.location.href + 'validate';
            data=$('#url-validation-form').serialize();
            $.post(url, data, function(data) {
                jdata=$.parseJSON(data);
                if(jdata.validationStatus === true) {
                    addMessage('Successful validation ' + jdata.url + '.');
                } else {
                    addError('Failed validation for ' + jdata.url +'.');
                }
            });
        }
    });
});
