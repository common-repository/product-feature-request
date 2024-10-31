var thpfqw_public = (function($, window, document) {
      'use strict';
 
    function feature_request_datas(elm) {
        requested_datas($(elm),event);
    }
        
    function requested_datas(click,event) {
        event.preventDefault();
        var form  = click.closest('form');
        var UserPost = $(form).serializeArray();
        var wrapper = $("#thpfr_appen_datas"); 
        var closest = click.closest('#tab-thpfr_custom_tab');
        jQuery.ajax({
            type : "POST",
            dataType : "json",
            url : get_request.ajax_url,
            data : UserPost,
            beforeSend : function() {
                auto_lodder_settings(wrapper,closest);
            },
            success: function(response) {
                handil_uppend_datas(wrapper,closest,response,click); 
            },
            fail: function() {
               alert('fail');
            },
        });
    }

    function handil_uppend_datas(wrapper,closest,response,click) {
        if (response.user_post_id && response.feature_title) {
            var new_frw_form = $('#thpfr_html_form_datas').html();
            wrapper.prepend(new_frw_form); 
            setTimeout(function(){$(".thpfr-show-notification").fadeOut(500);},2000);

            $('.thpfr-all-field').val('');
            $(closest).find('.thpfr-text').html('');
            $('.thpfr-form-fields').val('');
            $('.thpfr-user-vote .thpfr-icon').data('voter_pid',response.user_post_id);
            $('.thpfr-auto-lodder').removeClass('thpfr-show-auto-lodder');
            $('.th_Submit').css('pointer-events','all');
            $('.thpfr-hide-notification').addClass('thpfr-show-notification').css('display','block');

            $(closest).find('#thpfr_appen_datas .thpfr-feature-title').html('<b>'+response.feature_title+'</b>').removeClass('thpfr-feature-title');
            $(closest).find('#thpfr_appen_datas .thpfr-request-date-and-name').html(response.user_name_and_date);
            $(closest).find('#thpfr_appen_datas .thpfr-feature-request-status').html(response.feature_status);
            $(closest).find('#thpfr_appen_datas .thpfr-voter-pids').removeClass('thpfr-user-vote');

            if(response.user_request) {
                $(closest).find('#thpfr_appen_datas .thpfr-feature-request').html(response.user_request).removeClass('thpfr-feature-request');
            }
        }else {
            $(closest).find('.thpfr-text').html(response.name);

            if (response.verify_nonce) {
                $(closest).find('.thpfr-auto-lodder-wrapper').html(response.verify_nonce);
            } 
        } 
    }

    function auto_lodder_settings(wrapper,closest) {
        var title_empty_value = [];
        $('.thpfr-hide-notification').removeClass('thpfr-show-notification');
        $(".thpfr-form .thpfr-form-fields").each(function(index) { 
            var text_value = this.value.trim();  
            var feature_title = (index == 0) ? text_value : '';

            if(feature_title ) {
                title_empty_value.push(text_value);  
                $('.thpfr-remove-html').html('');
            }

            if(text_value == '') {
                $('.thpfr-hide-notification').css('display','none');
            }
        }); 
           
        if(title_empty_value.length) {
            $('.thpfr-auto-lodder').addClass('thpfr-show-auto-lodder');
            $('.th_Submit').css('pointer-events','none');
        }
    }

    function feature_request_voting(elm) {
        vote_btn_for_login($(elm));
        rate_feature_request($(elm),event);
    }

    function vote_btn_for_login(elm) {
        $('.thpfr-login-panel.login').addClass('thpfr-selectable'); 
        $('.thpfr-custom-tab-wrapper .thpfr-form').removeClass('thpfr-hide-form');
        $('.thpfr-custom-tab-wrapper .thpfr-login-form').removeClass('thpfr-hide-form');
        $('.thpfr-custom-tab-wrapper .thpfr-login-settings').css('display','none');
        $('html, body').animate({scrollTop:element_focuse($('.thpfr-custom-tab-wrapper'))}, 1000);
    }

    function element_focuse(elm) {
        if ($('.thwpfr-check-class').hasClass('thpfr-custom-tab-wrapper')) {
            return parseInt(elm.offset().top)-200;
        }
    }

    function rate_feature_request(click,evnt) {
        var wrapper = click.closest('.thpfr-voting-table');
        var post_id_value =  wrapper.find('.thpfr-voter-pids .thpfr-icon').data('voter_pid');

        evnt.preventDefault(evnt);
        var dataset = click.data();
        var data = [];
        for (var key in dataset) {
            if (dataset.hasOwnProperty(key)) {
                data.push({name:key, value:dataset[key]}); 
            } 
        }
        
        jQuery.ajax({
            type : "POST",
            dataType : "json",
            url : get_request.ajax_url,
            data : data,
            beforeSend : function() { 
                click.closest('.thpfr-voting-table').find('.thpfr-icon').removeClass('thpfr-user-vote-btn-disable');
                click.addClass('thpfr-user-vote-btn-disable');
                wrapper.find('.thpfr-vote-counter').addClass('thpfr-hide-load-voting');
                wrapper.find('.thpfr-load-voting').removeClass('thpfr-hide-load-voting');
            },
            success: function(response) {
                setup_votecounter_settings(response,wrapper,click);
            },
            fail: function() {
                alert('fail');
            },
        });
    }

    function setup_votecounter_settings(response,wrapper,click) {
        var vote_type = response.vote_count == 1 ? 'Vote' : 'Votes';
        wrapper.find('.thpfr-vote-counter').html(response.vote_count+' '+vote_type);
        wrapper.find('.thpfr-vote-counter').removeClass('thpfr-hide-load-voting');
        wrapper.find('.thpfr-load-voting').addClass('thpfr-hide-load-voting');

         if (response.toggle_value == 1) {
            wrapper.find('.thpfr-upvt').addClass('thpfr-user-vote-btn-inactive').removeClass('thpfr-user-vote-btn-active');
            wrapper.find('.thpfr-downvt').addClass('thpfr-user-vote-btn-active').removeClass('thpfr-user-vote-btn-inactive');
        }else { 
            wrapper.find('.thpfr-upvt').addClass('thpfr-user-vote-btn-active').removeClass('thpfr-user-vote-btn-inactive');
            wrapper.find('.thpfr-downvt').addClass('thpfr-user-vote-btn-inactive').removeClass('thpfr-user-vote-btn-active'); 
        }

        if (response.verify_nonce) {
            click.closest('.thpfr-title-wrapper').find('.thpfr-voting-table').html(response.verify_nonce);
        }
    }

    function feature_request_form_settings(elm) {
        event.preventDefault(event);
        $('.thpfr-form').toggleClass('thpfr-hide-form');
    }

    function user_login_settings(elm) {
       event.preventDefault(event);
       $('.thpfr-login-panel').removeClass('thpfr-selectable'); 
       $('.thpfr-registration-form').addClass('thpfr-hide-form');
       $('.thpfr-login-form').removeClass('thpfr-hide-form'); 
       $('.thpfr-login-settings').addClass('thpfr-hide-form');  
       $(elm).addClass('thpfr-selectable');    
    }

    function feature_registr_form_settings(elm) {
        event.preventDefault(event);
        $('.thpfr-login-panel').removeClass('thpfr-selectable'); 
        $('.thpfr-login-form').addClass('thpfr-hide-form');
        $('.thpfr-registration-form').removeClass('thpfr-hide-form');
        $('.thpfr-login-settings').addClass('thpfr-hide-form');
        $(elm).addClass('thpfr-selectable'); 
    }

    return{
        SetUprequest : feature_request_datas,
        SetUpvoting : feature_request_voting,
        SetupUserLogin : user_login_settings,
        ShowRequestForm : feature_request_form_settings,
        ShowRegistrForm : feature_registr_form_settings,
    }

}(window.jQuery, window, document));

function thpfrsubmit(elm) {
    thpfqw_public.SetUprequest(elm);
}

function VoteforFeatureRequest(elm) {
    thpfqw_public.SetUpvoting(elm);
}

function UserLoginSettings(elm) {
    thpfqw_public.SetupUserLogin(elm);
}

function ShowFeatureRequest(elm) {
    thpfqw_public.ShowRequestForm(elm);
}

function ShowFeatureRegisterForm(elm) {
    thpfqw_public.ShowRegistrForm(elm);
}