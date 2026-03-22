/**
 * Javascript functions to show top nitification
 * Error/Success/Info/Warning messages
 * Developed By: Ravi Tamada
 * url: http://androidhive.info
 * © androidhive.info
 * 
 * Created On: 10/4/2011
 * version 1.0
 * 
 * Usage: call this function with params 
 showNotification(params);
 **/

function showNotification(params){
    // options array
    var options = { 
        'showAfter': 0, // number of sec to wait after page loads
        'duration': 0, // display duration
        'autoClose' : false, // flag to autoClose notification message
        'type' : 'success', // type of info message error/success/info/warning
        'message': '', // message to dispaly
        'link_notification' : '', // link flag to show extra description
        'description' : '' // link to desciption to display on clicking link message
    }; 
    // Extending array from params
    $.extend(true, options, params);
    
    var msgclass = 'succ_bg'; // default success message will shown
    msgclass = 'alert-success';
    if(options['type'] == 'error'){
        msgclass = 'error_bg'; // over write the message to error message
        msgclass = 'alert-error';
    } else if(options['type'] == 'information'){
        msgclass = 'info_bg'; // over write the message to information message
        msgclass = 'alert-info';
    } else if(options['type'] == 'warning'){
        msgclass = 'warn_bg'; // over write the message to warning message
        msgclass = 'alert-block';
    } 
    // Parent Div container
    var container = '<div id="info_message" class="alert '+msgclass+'">';
	container += '<button type="button" class="close" data-dismiss="alert">×</button>';
	container += '<h4>Alerta!</h4>';
    container += options['message'];
    container += '</div>';
    
    $notification = $(container);
    
    // Appeding notification to Body
    $('body').append($notification);
    
    var divHeight = $('div#info_message').height();
    // see CSS top to minus of div height
    $('div#info_message').css({
        top : '-'+divHeight+'px'
    });
    
    // showing notification message, default it will be hidden
    $('div#info_message').hide().show();
    
    // Slide Down notification message after startAfter seconds
    slideDownNotification(options['showAfter'], options['autoClose'],options['duration']);
    
    $('.link_notification').live('click', function(){
        $('.info_more_descrption').html(options['description']).slideDown('fast');
    });
    
}
// function to close notification message
// slideUp the message
function closeNotification(duration){
    var divHeight = $('div#info_message').height();
    setTimeout(function(){
        $('div#info_message').animate({
            top: '-'+divHeight
        }); 
        // removing the notification from body
        setTimeout(function(){
            $('div#info_message').remove();
        },200);
    }, parseInt(duration * 1000));   
    

    
}

// sliding down the notification
function slideDownNotification(startAfter, autoClose, duration){    
    setTimeout(function(){
        $('div#info_message').animate({
            top:0
        }); 
        if(autoClose){
            setTimeout(function(){
                closeNotification(duration);
            }, duration);
        }
    }, parseInt(startAfter * 1000));    
}




