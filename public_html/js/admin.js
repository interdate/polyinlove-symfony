$(document).ready(
    function () {

        $.ajax({
            url: '/admin/users/there-for-count',
            success: function (res) {
                // alert(res);
                if (parseInt(res) > 0) {
                    $('.userNoForLength').text(parseInt(res));
                }
            }
        })


        $('.ui.accordion')
            .accordion()
        ;

        $('.ui .checkbox').checkbox();

        $('#search button').click(function () {

            if ($(this).hasClass('visible')) {
                $(this).removeClass('visible');
                $(this).parent().css('margin-left', '0px');
                $("#main_sidebar").animate({width: 'toggle'}, '25%');
            } else {
                $(this).addClass('visible');
                $(this).parent().css('margin-left', '25%');
                $("#main_sidebar").animate({width: 'toggle'}, '25%');
            }

        });


        $('#main_sidebar .scroll')
            .css({"height": $(window).height() - $('#logo').height()})
            .perfectScrollbar()
        ;

        //$('#main_sidebar .scroll').css({"margin-right": "5px"});


        $('.internal_sidebar .scroll')
            .css({"height": $(window).height() - $('#header').height()})
            .perfectScrollbar()
        ;


        /******************** User Photos And Cloudinary *********************************************/

        // $.cloudinary.config({ cloud_name: 'interdate', api_key: '771234826869846'});
        $('.ui.progress').hide();


        $('table.users .user_photo').each(function () {

            var noPhoto = false;
            var photoName = $(this).find('input[type="hidden"]').val();

            if (photoName.length) {
                // var url = $.cloudinary.url(photoName, { width: 50, height: 50, crop: 'thumb', gravity: 'face', radius: 'max' })
            } else {
                var url = ($(this).parents('tr').find('.user_props i').hasClass('male'))
                    ? $('#no_photo_male_url').val()
                    : ($(this).parents('tr').find('.user_props i').hasClass('female')) ?
                        $('#no_photo_female_url').val()
                        : $('#no_photo_a-binary_url').val();
                noPhoto = true;
            }

            $(this).find('img').attr('src', url);

            if (noPhoto) {
                $(this).find('img').addClass("ui circular image").css({"width": "50px", "height": "50px"});
            }

        });

        // $('table.waiting_photos .photo').each(function () {
        //     var photoName = $(this).find('input[type="hidden"]').val();
        //     $(this).find('img').attr('src', $.cloudinary.url(photoName, {width: 414, height: 491, crop: 'fill'}));
        // });

        /************Articles Cloudinary************/

        if ($('#article_imageName').size() && $('#article_imageName').val().length > 0) {
            var url = $.cloudinary.url($('#article_imageName').val(), {width: 300, height: 300, crop: 'fill'});
            $('.article_image').find('img').attr('src', url);
        }

        // $('.cloudinary-fileupload').bind('fileuploadstart', function (e, data) {
        //     $('.ui.progress').show();
        //     $('.upload_photo button').addClass("loading");
        // });
        //
        // $('.cloudinary-fileupload').bind('fileuploadprogress', function (e, data) {
        //     var value = Math.round((data.loaded * 100.0) / data.total);
        //     $('.ui.progress').progress({
        //         percent: value,
        //     });
        //     $('#upload_photo_label span').text(value);
        // });
        //
        // $('.cloudinary-fileupload').bind('cloudinarydone', function (e, data) {
        //     //console.log(JSON.stringify(data));
        //     $('.upload_photo button').removeClass("loading");
        //     $('.ui.progress').hide();
        //     $('#article_imageName').val(data.result.public_id);
        //     var url = $.cloudinary.url(data.result.public_id, {width: 300, height: 300, crop: 'fill'});
        //     $('.article_image').find('img').attr('src', url);
        //     return true;
        // });


        /********************************************************************************************/


        $('.user_props .icon, .manage_user .icon, #logo, .users .actions button, .users .user_photo img').popup();
        $('.waiting_photos .card button, .waiting_photos .username, .waiting_photos .manage_photos, .waiting_photos .manage_user').popup();
        $('.waiting_photos .meta .icon').popup();

        $('#confirm_del').popup({
            on: 'click'
        });


        $('.users .paging a').click(function (e) {
            e.preventDefault();
            var url = $(this).attr('href');

            $('#search_filter_form')
                .attr('action', url)
                .find('input[type="submit"]')
                .click()
            ;
        });


        $('.users .ui.checkbox.toggle.is_active').checkbox({
            onChecked: function () {
                console.log($(this));
                setUserProperty('isActive', 1, $(this).parents('tr').find('.userId').val());
            },
            onUnchecked: function () {
                var userId = $(this).parents('tr').find('.userId').val();
                // alert(2);
                setUserProperty('isActive', 0, userId);

                var users = []
                users.push(userId);

                $('.small.modal.ban_users_reason')
                    .modal({
                        onApprove: function () {
                            saveBanUsersReason(users, $(this).find('textarea').val());
                        },
                        onHidden: function () {
                            $(this).find('textarea').val('');
                        }
                    })
                    //.modal('setting', 'transition', 'fade up')
                    .modal('show')
                ;
            },
        });


        //$('.sel_item .ui.checkbox').checkbox('attach events', '#sel_all'); // toggle

        //$('.sel_all').checkbox();

        $('#sel_all').click(function () {
            if ($(this).find('input[type="checkbox"]').is(":checked")) {
                $('.sel_item').find('.ui.checkbox').checkbox('set checked');
            } else {
                $('.sel_item').find('.ui.checkbox').checkbox('set unchecked');
            }
        });


        $('.sel_all').click(function () {
            var selected = $(this).siblings('input[type="hidden"]');
            if (selected.val() == 0) {
                $('.sel_item').find('.ui.checkbox').checkbox('set checked');
                selected.val(1);
            } else {
                $('.sel_item').find('.ui.checkbox').checkbox('set unchecked');
                selected.val(0);
            }
        });


        $('.users .activate, .users .deactivate').click(function () {

            //$(this).addClass('loading');


            var users = [];
            var value = 1;
            var state = ":not(:checked)";

            if ($(this).hasClass('deactivate')) {
                value = 0;
                state = ":checked";
            }

            $('.sel_item .ui.checkbox').each(function () {
                if ($(this).find('input[type="checkbox"]').is(":checked")) {
                    var checkbox = $(this).parents('tr').find('.ui.checkbox.is_active input[type="checkbox"]');
                    if (checkbox.is(state) && checkbox.is(":not(:disabled)")) {
                        var userId = $(this).parents('tr').find('.userId').val();
                        checkbox.click();
                        // alert(1);
                        setUserProperty('isActive', value, userId);
                        users.push(userId);
                    }
                }
            });

            if (value == 0 && $('.sel_item .ui.checkbox input[type="checkbox"]').is(":checked")) {
                $('.small.modal.ban_users_reason')
                    .modal({
                        onApprove: function () {
                            saveBanUsersReason(users, $(this).find('textarea').val());
                        },
                        onHidden: function () {
                            $(this).find('textarea').val('');
                        }
                    })
                    //.modal('setting', 'transition', 'fade up')
                    .modal('show')
                ;
            }


            //$(this).removeClass('loading');

        });


        $('.is_activated').click(function (e) {
            var state = $(this).hasClass('checked') ? 1 : 0;
            var userId = $(this).parents('tr').find('.userId').val();
            console.log(userId)
            // if($(this).hasClass('checked')) {
            //     alert('check');
            //
            // } else {
            //     alert('uncheck')
            // }
            $.ajax({
                url: '/admin/user/' + userId + '/isActivated/' + state,
            });
        });


        $('.users .freeze, .users .unfreeze').click(function () {

            var value = ($(this).hasClass('freeze')) ? 1 : 0;

            $('.sel_item .ui.checkbox').each(function () {
                if ($(this).find('input[type="checkbox"]').is(":checked")) {
                    setUserProperty('isFrozen', value, $(this).parents('tr').find('.userId').val(), 'asterisk');
                }
            });

        });

        $('.users .flag, .users .unflag').click(function () {

            var value = ($(this).hasClass('flag')) ? 1 : 0;

            $('.sel_item .ui.checkbox').each(function () {
                if ($(this).find('input[type="checkbox"]').is(":checked")) {
                    setUserProperty('isFlagged', value, $(this).parents('tr').find('.userId').val(), 'flag');
                }
            });

        });


        $('.users .delete').click(function () {

            if ($('.sel_item .ui.checkbox input[type="checkbox"]').is(":checked")) {
                if (confirm('remove selected users?')) {
                    $('.sel_item .ui.checkbox').each(function () {
                        if ($(this).find('input[type="checkbox"]').is(":checked")) {
                            deleteUser($(this).parents('tr').find('.userId').val());
                            $(this).parents('tr').remove();
                        }
                    });
                }
            }
            s
        });


        $('.users .report').click(function () {

            $('.small.modal.create_report')
                .modal({
                    onApprove: function () {
                        createReport($(this));
                    },
                    onHidden: function () {
                        $(this).find('input[type="text"]').val('');
                        $(this).find('input[type="checkbox"]').attr('checked', '');
                    }
                })
                //.modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .export').click(function () {

            $('.small.modal.export')
                .modal({
                    onApprove: function () {
                        exportToCSV($(this));
                    },
                    onHidden: function () {
                        $(this).find('input[type="text"]').val('');
                    }
                })
                //.modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .point').click(function () {

            $('.small.modal.give_point')
                .modal({
                    onApprove: function () {
                        givePoint(true);
                    },
                    onDeny: function () {
                        givePoint();
                    }
                })
                .modal('setting', 'transition', 'fade up')
                .modal('show')
            ;
        });

        $('.users .user_photo img').click(function () {
            getUserPhotosModal($(this).parents('tr').find('.userId').val());
        });

        $('.users .username').click(function () {
            viewProfile($(this).parents('tr').find('.userId').val());
        });

        $('.messages .profile').click(function () {
            viewProfile($(this).siblings('.userId').val());
        });

        $('.users .manage_user .edit').click(function () {
            getEditedProfile($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .diamond').click(function () {
            getSubscr($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .sign.in').click(function () {
            logInAsUser($(this).parents('tr').find('.userId').val());
        });

        $('.users .manage_user .envelope').click(function () {
            sendAdminMessage($(this).parents('tr').find('.userId').val(), $(this).parents('tr').find('.userUsername').val());
        });

        $('.users .manage_user .addVerify').click(function () {
            verifyUser($(this).parents('tr').find('.userId').val(), $(this));
        })

        $('.users .manage_user .check.disabled').click(function () {
            var elem = $(this);
            $.ajax({
                url: '/admin/users/user/verify/remove/' + $(this).parents('tr').find('.userId').val(),
                success: function () {
                    elem.removeClass('disabled');
                    elem.attr('data-content', 'verify user');
                }
            })
        });


        /*
        $( ".field .birthdayCalendar" ).datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '-90:-18',
            defaultDate:'-18y-m-d',
        });
        */


        $('.advanced_search .calendar input').datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '-10:+1',
            defaultDate: 'y-m-d',
            dateFormat: 'dd/mm/yy',
        });


        $('.advanced_search .period select').change(function () {
            var dateObj1 = $(this).parent().siblings('.date_from').find('input[type="text"]');
            var dateObj2 = $(this).parent().siblings('.date_to').find('input[type="text"]');
            setDatePeriods(dateObj1, dateObj2, $(this).val(), '%dd/%mm/%Y');
        });


        $('.waiting_photos .actions .approve, .waiting_photos .actions .delete').click(function () {

            var approve = true;

            if ($(this).hasClass('delete')) {
                if (!confirm('remove images?')) {
                    return;
                }
                approve = false;
            }

            var checkedImages = [];
            $('.sel_item .ui.checkbox').each(function () {
                var checkbox = $(this).find('input[type="checkbox"]');

                if (checkbox.is(":checked")) {
                    var id = $(this).siblings('input[type="hidden"]').val();
                    checkedImages.push(id);

                    console.log(checkedImages);
                    // approvePhoto(id, approve);

                }
            });
            approvePhoto(checkedImages, approve);
        });

        $('.waiting_photos .card .approve, .waiting_photos .card .delete').click(function () {

            var approve = true;


            if ($(this).hasClass('delete')) {
                if (!confirm('remove image?')) {
                    return;
                }
                approve = false;
            }

            var id = $(this).parents('.card').find('.sel_item input[type="hidden"]').val();
            approvePhoto(id, approve);

        });

        $('.waiting_photos .username').click(function () {
            viewProfile($(this).parents('.card').find('.userId').val());
        });

        $('.waiting_photos .manage_photos').click(function () {
            getUserPhotosModal($(this).parents('.card').find('.userId').val());
        });

        $('.waiting_photos .manage_user').click(function () {
            getEditedProfile($(this).parents('.card').find('.userId').val());
        });

        $('.flagged_reports .item').click(function () {
            $(this).find('form').submit();
        });

        $('.reports a').click(function (e) {
            e.preventDefault();
            $(this).siblings('form').submit();
        });

        $('.reports .ui.checkbox.toggle').checkbox({
            onChecked: function () {
                showReportOnMainPage(1, $(this).parents('tr').find('.report_id').val());
            },
            onUnchecked: function () {
                showReportOnMainPage(0, $(this).parents('tr').find('.report_id').val());
            },
        });

        $('.reports .delete').click(function () {
            var name = $(this).parents('tr').find('.report_name').text();
            if (confirm('remove report ' + name + '?')) {
                deleteReport($(this));
            }
        });

        $('.articles .delete').click(function () {
            var name = $(this).parents('tr').find('.article_name').text();
            if (confirm('remove paper - ' + name + '?')) {
                deleteArticle($(this));
            }
        });

        $('.pages .delete').click(function () {
            var name = $(this).parents('tr').find('.page_name').text();
            if (confirm('remove page - ' + name + '?')) {
                deletePage($(this));
            }
        });

        $('.articles .ui.checkbox.toggle').checkbox({
            onChecked: function () {
                setArticleProperty($(this), 1);
            },
            onUnchecked: function () {
                setArticleProperty($(this), 0);
            },
        });

        $('.pages .ui.checkbox.toggle').checkbox({
            onChecked: function () {
                setPageProperty($(this), 1);
            },
            onUnchecked: function () {
                setPageProperty($(this), 0);
            },
        });


        $('.special.cards .image').dimmer({
            on: 'hover'
        });

        $('.edit_slide').click(function () {
            editSlide($(this).parents('.card').find('input[type="hidden"]').val());
        });

        $('.slide').each(function () {
            var photoName = $(this).siblings('input[type="hidden"]').val();
            // $(this).attr('src', $.cloudinary.url(photoName, { width: 300}));
        });

        $('.headers .button').click(function () {
            $(this).parents('.headers').find('.button').removeClass('olive');
            $(this).addClass('olive');
            $(this).siblings('input[type="hidden"]').val($(this).text());
        });

        $('.footerHeaders .save.icon').click(function () {
            saveFooterHeader($(this).parent());
        });


        $('#removeSelectedMessages').click(function () {
            var messagesIds = [];

            $('.messages .row :checked').each(function () {
                messagesIds.push($(this).val());
            });
            removeSelectedMessages(messagesIds);
        });

        $('is_activated').click(function () {
            console.log($(this).parent());
        });

        faqInit();
        ajaxHelper();

        $('.bannerActive').click(function () {
            var id = $(this).parents('tr').find('.banner_id').val();
            $.ajax({
                url: '/admin/banner/' + id + '/activate',
            });
        });

        $('#sendAdminMessage').click(function () {
                //console.log($(this).parents('.adminPrivateMessage').find('.user_id').val())
                var contactId = $(this).parents('.adminPrivateMessage').find('.content').find('.contact_id').val();
                var userId = $(this).parents('.adminPrivateMessage').find('.content').find('.user_id').val();
                var apiKey = $('#apiKey').val();
                var message = $(this).parents('.adminPrivateMessage').find('.content').find('#sendMessage').val();
                $.ajax({
                    url: '/messenger/message/send/userId:' + userId + '/contactId:' + contactId,
                    headers: {'apiKey': apiKey},
                    //url: '/chat/index.php?sendMessage=true&userId='+Messenger.currentUserId+'&contactId='+this.contactId,
                    timeout: 80000,
                    dataType: 'json',
                    type: 'Post',
                    data: {
                        message: message,
                        fromAdmin: true,
                    }, //'message=' + encodeURIComponent(message) + 'fromAdmin=true',
                    context: this,
                    // contentType: '; charset=UTF-8',
                    success: function (res) {
                        if (res.success) {
                            $.ajax({
                                url: '/messenger/message/send/push/userId:111/contactId:' + contactId,
                            });
                            alert('Message sent')
                        }
                    }
                });
            }
        );

        $("#banner_img").change(function () {
            readURL(this);
        });


    });


// function testfnc(e, id) {
//     e.preventDefault();
//     var files = $('#photo22')[0].files[0];
//     console.log(files);
//     var jso  = JSON.stringify(files);
//     console.log(jso);
//     JSON.enc
//     // var formData = new FormData($('#testForm')[0]);
//    //  console.log(formData);
//    $.ajax({
//        url: '/admin/users/user/' + id + '/photos/photo',
//        type: "POST",
//        data: {'photo': files},
//        success: function (response) {
//            //
//        }
//    })
// }


function logInAsUser(id) {
    window.location.href = '/admin/users/login/' + id;
}


function givePoint(giveToAll) {

    var state = giveToAll ? 1 : 0;

    $.ajax({
        url: '/admin/users/point/' + state,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });

}


function exportToCSV(exportWindow) {

    var fileName = exportWindow.find('input[type="text"]').val().trim();

    if (!fileName) {
        alert('File name is empty');
        return;
    }

    $('#search_filter_form')
        .attr('action', '/he/admin/users/export')
        .find('input[name="fileName"]')
        .val(fileName)

    $.ajax({
        url: '/he/admin/users/export',
        type: 'post',
        postType: 'application/json',
        data: $('#search_filter_form').serialize(),
        success: function (res) {
            window.open('/downloadcsv.php?filename=' + res.fileName
                + '&originalName=' + res.originalName, '_blank')
        }
    })
}


function deleteReport(thisObj) {

    var id = thisObj.parents('tr').find('.report_id').val();

    $.ajax({
        url: '/admin/users/reports/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
        }
    });

}

function showReportOnMainPage(state, id) {

    $.ajax({
        url: '/admin/users/reports/' + id + '/show_on_main_page/' + state,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });

}

function createReport(createReportWindow) {

    if (!createReportWindow.find('input[type="text"]').val().trim()) {
        alert('Could not create report');
        return;
    }

    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    var reportSettings = createReportWindow.find('form').serialize();
    var reportData = $('#search_filter_form').serialize();

    $.ajax({
        url: '/admin/users/reports/create',
        type: 'Post',
        data: reportSettings + '&' + reportData,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            location.reload(true);
        }
    });


}

function approvePhoto(ids, approve) {

    approve = approve ? 1 : 0;

    console.log(ids);
    if (Array.isArray(ids)) {
        for (var i = 0; i < ids.length; i++) {
            $('input[value="' + ids[i] + '"]').parents('.card').remove();
            $('.ui.popup.top.center.transition.visible').remove();
        }
    } else {
        $('input[value="' + ids + '"]').parents('.card').remove();
        $('.ui.popup.top.center.transition.visible').remove();
    }
    $.ajax({
        url: '/admin/users/photos/waiting/' + ids + '/approve/' + approve,
        type: 'Get',
        error: function (response) {
            alert(1)
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            console.log($('.waiting_photos .card').size());

            if (!$('.waiting_photos .card').size()) {
                $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
                $('.waiting_photos .actions').remove();
                window.location.href = '/admin/users/list';
            }
        }
    });
}


function viewProfile(id) {
    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/admin/users/view/profile/' + id,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#viewed_user_data').html(response).kfModal();
            $('#viewed_user_data .menu .item').tab();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}


function getSubscr(id) {
    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/admin/users/user/' + id + '/subscription',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#subscr').html(response).kfModal();
            $('#subscr .menu .item').tab();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function getEditedProfile(id) {

    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    // alert('/admin/users/edit/profile/' + id);
    const appdev = window.location.href.includes('app_dev.php') ? '/app_dev.php' : '';

    $.ajax({
        url: appdev + '/admin/users/edit/profile/' + id,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            console.log('great success!')
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
            $('#edited_user_data').html(response).kfModal();
            $('#edited_user_data .menu .item').tab();
        }
    });
}


function getUserPhotosModal(id) {
    //$('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    $('#user_photos').find('.photos').html('');
    $('#user_photos').kfModal();
    getUserPhotos(id);
}


function getUserPhotos(id) {

    $('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/admin/users/user/' + id + '/photos',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#user_photos').find('.photos').html(response);
            $('#user_photos_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });

}


function savePhotoData(data, userId) {

    /// alert(1)
    $('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    var mainPhotoAlreadyExists = $('#mainPhotoAlreadyExists').val();

    $.ajax({
        url: '/admin/users/user/' + userId + '/photos/photo/data',
        type: 'Post',
        data: 'name=' + data.result.public_id + '&mainPhotoAlreadyExists=' + mainPhotoAlreadyExists,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function () {
            getUserPhotos(userId);
        }
    });
}

function setPhotoProperty(property, value, id, elem) {

    console.log(property, value, id);

    $.ajax({
        url: '/admin/users/user/photos/' + id + '/' + property + '/' + value,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            if (property === 'isMain') {
                console.log($('.private_photo input'));
                console.log(elem);
                $('.private_photo input').attr('disabled', false);
                elem.parents('tr').find('.private_photo input').attr('disabled', true)
            } else if (property === 'isPrivate') {
                console.log(Boolean(value));
                elem.parents('tr').find('.main_photo input').attr('disabled', Boolean(value))
            }
        }
    });

}

function ajaxHelper() {
    if ($('#ajaxHelper').length > 0) {
        $.ajax({
            url: '/admin/ajax/users/list',
            type: 'Get',
            dataType: 'json',
            data: $('#ajaxHelper').serialize(),
            error: function (response) {
                console.log("Error: ");
                console.log(response);
            },
            success: function (response) {
                // console.log(response);
                $('.usersLength').html(response.count);
                for (var i in response.reports) {
                    var report = response.reports[i];
                    $('.flagged_reports .item input[value="' + report.id + '"]').parents('.item').find('.ui.label').html(report.count);
                }
                // console.log(response);
            }
        });
    }
}


function deletePhoto(id, node) {

    $('#user_photos_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    var thisIsMainPhoto = node.find('.main_photo input').is(":checked");
    node.remove();

    if (thisIsMainPhoto) {
        $('.photo').eq(0).find('.main_photo').click();
    }

    $.ajax({
        url: '/admin/users/user/photos/' + id + '/delete',
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (otherPhotoId) {
            if (otherPhotoId > 0) {
                var radiobox = $('.photos .main_photo').find('input[value="' + otherPhotoId + '"]');
                radiobox.attr('disabled', false);
                radiobox.parents('.photos .main_photo').checkbox('attach events', radiobox, 'check').click();
            }
            if (!$('.photos .photo').size()) {
                $('#mainPhotoAlreadyExists').val(0);
            }

            $('#user_photos_dimmer').addClass("disabled").find('.loader').addClass("disabled");

        }
    });
}

function saveProfile(id, form, tab) {

    if ((typeof (form[0].checkValidity) == "function") && !form[0].checkValidity()) {
        return;
    }

    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, select, textarea').prop("disabled", true);
    const appdev = window.location.href.includes('app_dev.php') ? '/app_dev.php' : '';

    $.ajax({
        url: appdev + '/admin/users/edit/profile/' + id + '/' + tab,
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#edited_user_data')
                .html(response)
                .find('.menu .item')
                .tab()
            ;
        }
    });
}

function setUserProperty(property, value, userId, icon) {
    // alert(1)
    $.ajax({
        url: '/admin/user/' + userId + '/' + property + '/' + value,
        type: 'Post',
        error: function (response) {
            console.log("Error:" + response);
        },
        success: function (response) {
            console.log(response);
            if (icon) {
                var item = $('.users input[value="' + userId + '"]').parents('tr').find('.user_props i.icon.' + icon);
                if (value == 1) {
                    item.removeClass('hidden');
                } else {
                    item.addClass('hidden');
                }
            }
        }
    });

}

function deleteUser(userId) {
    $.ajax({
        url: '/admin/user/' + userId + '/delete',
        type: 'Post',
        error: function (response) {
            console.log("Error:" + response);
        },
        success: function (response) {
            console.log(response);
            //alert('בוצע');
        }
    });

}

function saveBanUsersReason(users, reason) {

    if (!reason.trim().length || !users.length) {
        return;
    }

    $.ajax({
        url: '/admin/users/save/ban/reason',
        type: 'Post',
        data: 'users=' + users + '&reason=' + reason,
        error: function (response) {
            console.log("Error:" + response);
        },
        success: function (response) {
            console.log(response);
        }
    });

}


function setArticleProperty(thisObj, value) {

    var id = thisObj.parents('tr').find('.article_id').val();
    var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';

    $.ajax({
        url: '/admin/magazine/article/' + id + '/' + property + '/' + value,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });

}


function deleteArticle(thisObj) {

    var id = thisObj.parents('tr').find('.article_id').val();

    $.ajax({
        url: '/admin/magazine/article/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
        }
    });

}


function setPageProperty(thisObj, value) {

    var id = thisObj.parents('tr').find('.page_id').val();
    //var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';
    var property = 'isActive';

    $.ajax({
        url: '/admin/content/page/' + id + '/' + property + '/' + value,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });

}


function deletePage(thisObj) {

    var id = thisObj.parents('tr').find('.page_id').val();

    $.ajax({
        url: '/admin/content/page/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
        }
    });

}


function editSlide(id) {
    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/admin/content/slide/' + id,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#slide').html(response).kfModal();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveSlide(id, form) {
// alert(1)
//     if (( typeof(form[0].checkValidity) == "function" ) && !form[0].checkValidity()) {
//         alert(1);
//         return;
//     }

    var data = form.serialize();
    // var formData = new FormData();
    // formData.append("image", $('#image-slider').prop('files')[0]);
    // formData.append("data", form.serialize());


    form.find('button').addClass('loading');
    form.find('input, select, textarea').prop("disabled", true);

    $.ajax({
        url: '/admin/content/slide/' + id,
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

            $('#slide').html(response);

            $('.slides input[value="' + id + '"]')
                .parents('.card')
                .find('a.edit_slide')
                .text($('#slide_name').val())
            ;

            $('.slides input[value="' + id + '"]')
                .parents('.card')
                .find('.slide')
                .attr('src', $('.slide_image img').attr('src'))
            ;
        }
    });
}


function updateHomePageBlock(form, id) {

    var data = form.serialize();
    form.siblings('div').find('button').addClass('loading');
    form.find('input, select, textarea').prop("disabled", true);

    $.ajax({
        url: '/admin/content/homepage/block/' + id,
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            form.siblings('div').find('button').removeClass('loading');
            form.find('input, select, textarea').prop("disabled", false);
        }
    });
}


function updatePageSeo(form) {

    var data = form.serialize();
    form.siblings('div').find('button').addClass('loading');
    form.find('input, textarea').prop("disabled", true);

    $.ajax({
        url: '/admin/content/pages/seo',
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            form.siblings('div').find('button').removeClass('loading');
            form.find('input, textarea').prop("disabled", false);
        }
    });
}


function saveFooterHeader(wrapper) {

    console.log(1);
    var name = wrapper.find('input[type="text"]').val();
    var id = wrapper.find('input[type="hidden"]').val();
    wrapper.find('i').addClass('loading');
    wrapper.find('input').prop("disabled", true);

    $.ajax({
        url: '/admin/content/footer/header/' + id,
        type: 'Post',
        data: 'name=' + name,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            wrapper.find('i').removeClass('loading');
            wrapper.find('input').prop("disabled", false);
        }
    });
}


function faqCategory(id) {

    var url = (id ? '/admin/content/faq/category/' + id : '/admin/content/faq/category');

    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: url,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#faq_cat').html(response).kfModal();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveFaqCategory(id) {

    var form = $('#faq_cat_form');

    if ((typeof (form[0].checkValidity) == "function") && !form[0].checkValidity()) {
        return;
    }

    var url = (id ? '/admin/content/faq/category/' + id : '/admin/content/faq/category');
    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, checkbox').prop("disabled", true);

    $.ajax({
        url: url,
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('.ui.segment.faq').html(response);
            faqInit();
            form.find('button').removeClass('loading');
            form.find('input, checkbox').prop("disabled", false);
            $.kfModal.close();
            //updateFaqSection();
        }
    });
}

function updateFaqSection() {
    $.ajax({
        url: '/admin/content/faq/section/update',
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });
}


function faq(id) {

    var url = id ? '/admin/content/faq/' + id : '/admin/content/faq';

    $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: url,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('#faq').html(response).kfModal();
            $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function saveFaq(id) {

    var form = $('#faq_form');

    if ((typeof (form[0].checkValidity) == "function") && !form[0].checkValidity()) {
        return;
    }

    var url = (id ? '/admin/content/faq/' + id : '/admin/content/faq');
    var data = form.serialize();

    form.find('button').addClass('loading');
    form.find('input, checkbox, select, textarea').prop("disabled", true);

    $.ajax({
        url: url,
        type: 'Post',
        data: data,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            $('.ui.segment.faq').html(response);
            faqInit();
            form.find('button').removeClass('loading');
            form.find('input, checkbox, select, textarea').prop("disabled", false);
            $.kfModal.close();
            //updateFaqSection();
        }
    });
}


function setFaqCategoryProperty(thisObj, value) {

    var id = thisObj.parents('tr').find('.category_id').val();
    //var property = thisObj.parent().hasClass('homepage') ? 'isOnHomePage' : 'isActive';
    var property = 'isActive';

    $.ajax({
        url: '/admin/content/faq/category/' + id + '/' + property + '/' + value,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

        }
    });

}

function deleteFaqCategory(thisObj) {

    var id = thisObj.parents('tr').find('.category_id').val();

    $.ajax({
        url: '/admin/content/faq/category/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
            $('.faq_cat_' + id).remove();
        }
    });
}

function deleteBanner(thisObj) {

    var id = thisObj.parents('tr').find('.banner_id').val();

    $.ajax({
        url: '/admin/content/banner/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
            //  $('.faq_cat_' + id).remove();
        }
    });
}

function deleteFaq(thisObj) {

    var id = thisObj.parents('tr').find('.faq_id').val();

    $.ajax({
        url: '/admin/content/faq/' + id + '/delete',
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            thisObj.parents('tr').remove();
        }
    });

}

function faqInit() {
    $('.faq .menu .item').tab();

    $('.ui.accordion')
        .accordion()
    ;

    $('.add_faq_cat').click(function () {
        faqCategory();
    });

    $('.edit_faq_cat').click(function () {
        faqCategory($(this).siblings('input[type="hidden"]').val());
    });

    $('.add_faq').click(function () {
        faq();
    });

    $('.edit_faq').click(function () {
        //var idArr = $(this).attr('id').split("_");
        //faq(idArr[1]);
        faq($(this).siblings('input[type="hidden"]').val());
    });

    $('table.faq_categories .ui.checkbox.toggle').checkbox({
        onChecked: function () {
            setFaqCategoryProperty($(this), 1);
        },
        onUnchecked: function () {
            setFaqCategoryProperty($(this), 0);
        },
    });

    $('table.faq_categories .delete').click(function () {
        var name = $(this).parents('tr').find('.category_name').text();
        if (confirm('להסיר קטגוריה - ' + name + '?')) {
            deleteFaqCategory($(this));
        }
    });

    $('table.faq .delete').click(function () {
        var name = $(this).parents('tr').find('.faq_name').text();
        if (confirm('להסיר שאלה - ' + name + '?')) {
            deleteFaq($(this));
        }
    });

    $('table.banners .delete').click(function () {
        var name = $(this).parents('tr').find('.banner_id').text();
        console.log(name);
        if (confirm('להסיר באנר?')) {
            deleteBanner($(this));
        }
    });
}


function removeSelectedMessages(messagesIds) {

    if (!messagesIds.length) {
        alert('יש לבחור הודעות');
        return;
    }

    if (!confirm('למחוק?')) {
        return;
    }


    $.ajax({
        url: '/admin/messenger/messages/delete',
        data: 'messagesIds=' + messagesIds,
        type: 'Post',
        success: function () {

            $('.messages .row :checked').each(function () {
                $(this).parents('tr').remove();
            });

            alert('messages deleted');

        },
        error: function (response) {
            $('body').html(response.responseText);
        }
    });

}


function sendAdminMessage(id, username) {
    $('.ui.modal.adminPrivateMessage').modal({centered: true}).modal('show');
    var username =
        $('.ui.modal.adminPrivateMessage .header').text('send pm to user ' + username)
    $('.adminPrivateMessage').find('.contact_id').val(id);
}


//D4D old website function


function setDatePeriods(objRef_l, objRef_h, period, dtformat) {
    var d = new Date(),
        dStr_l = "",
        dStr_h = "",
        m;
    switch (period) {
        case "today":
            dStr_l = dStr_h = createdStr();
            break;
        case "week_t":
            d.setDate(d.getDate() - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_t":
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "quarter_t":
        case "3":
            m = d.getMonth();
            if (m <= 2) {
                d.setMonth(0)
            } else if (m <= 5) {
                d.setMonth(3)
            } else if (m <= 8) {
                d.setMonth(6)
            } else {
                d.setMonth(9)
            }
            ;
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_t":
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        case "week_n":
            d.setDate(d.getDate() + 7 - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_n":
            d.setMonth(d.getMonth() + 1);
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case 1:
        case 3:
        case 6:
        case 12:
            d = new Date(objRef_l.val());
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + period);
            dStr_h = createdStr();
            break;
        case 0:
            d = new Date(objRef_l.val());
            dStr_l = createdStr();
            dStr_h = createdStr();
            break;
        case "2weeks":
            d = new Date(objRef_l.val());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 14);
            dStr_h = createdStr();
            break;
        case "quarter_n":
            m = d.getMonth();
            if (m <= 2) {
                d.setMonth(3)
            } else if (m <= 5) {
                d.setMonth(6)
            } else if (m <= 8) {
                d.setMonth(9)
            } else {
                d.setMonth(12)
            }
            ;
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_n":
            d.setFullYear(d.getFullYear() + 1);
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        case "week_p":
            d.setDate(d.getDate() - 7 - d.getDay());
            dStr_l = createdStr();
            d.setDate(d.getDate() + 6);
            dStr_h = createdStr();
            break;
        case "month_p":
            d.setMonth(d.getMonth() - 1);
            d.setDate(1);
            dStr_l = createdStr();
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "quarter_p":
            m = d.getMonth();
            if (m <= 2) {
                d.setFullYear(d.getFullYear() - 1);
                d.setMonth(9)
            } else if (m <= 5) {
                d.setMonth(0)
            } else if (m <= 8) {
                d.setMonth(3)
            } else {
                d.setMonth(6)
            }
            ;
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(d.getMonth() + 2);
            setMonthLastDate();
            dStr_h = createdStr();
            break;
        case "year_p":
            d.setFullYear(d.getFullYear() - 1);
            d.setMonth(0);
            d.setDate(1);
            dStr_l = createdStr();
            d.setMonth(11);
            d.setDate(31);
            dStr_h = createdStr();
            break;
        default:
            return;
            break;
    }
    ;

    /*
         objRef_l.value = dStr_l;
         objRef_h.value = dStr_h;

    */
    //modified


    objRef_l.val(dStr_l);
    objRef_h.val(dStr_h);


    function setMonthLastDate() {
        m = d.getMonth();
        if (m == 0 || m == 2 || m == 4 || m == 6 || m == 7 || m == 9 || m == 11) {
            d.setDate(31)
        } else if (m == 3 || m == 5 || m == 8 || m == 10) {
            d.setDate(30)
        } else if (d.getFullYear() % 4 == 0) {
            d.setDate(29)
        } else {
            d.setDate(28)
        }
    }

    function createdStr() {
        if ("undefined" != typeof (dtformat)) {
            if (console && console.log) {
                console.log('in')
            }
            return formatDate_ami(d, dtformat);
        }
        return ((d.getMonth() + 1) < 10 ? "0" + (d.getMonth() + 1) : (d.getMonth() + 1)) + "/" + (d.getDate() < 10 ? "0" + d.getDate() : d.getDate()) + "/" + d.getFullYear()
    }

    function formatDate_ami(dt, s) {
        if (!(dt instanceof Date)) {
            throw "Date object expected";
        }
        s = s.toString();

        console.log(s);
        var day = dt.getDate();
        if (day < 10) day = '0' + day;
        var month = dt.getMonth() + 1;
        if (month < 10) month = '0' + month;
        s = s.replace(/%dd/g, day);
        s = s.replace(/%mm/g, month);
        s = s.replace(/%d/g, dt.getDate());
        s = s.replace(/%m/g, dt.getMonth() + 1);
        s = s.replace(/%Y/g, dt.getFullYear());
        s = s.replace(/%H/g, dt.getHours());
        s = s.replace(/%i/g, dt.getMinutes());
        s = s.replace(/%s/g, dt.getSeconds());
        s = s.replace(/%f/g, dt.getMilliseconds());
        return s;
    }
}

var updateImg = 0;

function rotateImage(id, rotate, image) {
    $.ajax({
        url: '/admin/users/user/photo/rotate',
        type: 'Post',
        data: 'id=' + id + '&rotate=' + rotate,
        dataType: 'json',
        error: function (response) {
            console.log("Error: " + response);
        },
        success: function (response) {
            console.log(response);
            var faceSrc = response.facePhoto + '?u=' + updateImg;
            var fullPhotoSrc = response.fullPhoto + '?u=' + updateImg;
            //alert(src);
            if (response.facePhoto) {
                //   response.url = response.url + '?u='+updateImg;
                updateImg++;
                var backImage = $('.user_photo img[data-photo-id="' + id + '"]');
                console.log(backImage);
                image.attr('src', fullPhotoSrc);
                backImage.attr('src', faceSrc);
            }
        }
    });
}

function verifyUser(id, elem) {

    $.ajax({
        url: '/user/verify/' + id,
        success: function (res) {
            console.log(elem);
            if (res) {
                alert('המשתש אומת');
                elem.addClass('disabled');
                elem.attr('data-content', 'המשתמש אומת');
                // console.log(elem)
                // elem.removeAttr('href');
                // elem.addClass('opacity-05');

            }
        }
    });
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('.banner-image').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}


function FindPosition(oElement) {
    if (typeof (oElement.offsetParent) != "undefined") {
        for (var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent) {
            posX += oElement.offsetLeft;
            posY += oElement.offsetTop;
        }
        return [posX, posY];
    } else {
        return [oElement.x, oElement.y];
    }
}

function GetCoordinates(e) {
    var PosX = 0;
    var PosY = 0;
    var ImgPos;
    ImgPos = FindPosition(myImg);
    if (!e) var e = window.event;
    if (e.pageX || e.pageY) {
        PosX = e.pageX;
        PosY = e.pageY;
    } else if (e.clientX || e.clientY) {
        PosX = e.clientX + document.body.scrollLeft
            + document.documentElement.scrollLeft;
        PosY = e.clientY + document.body.scrollTop
            + document.documentElement.scrollTop;
    }
    PosX = PosX - ImgPos[0];
    PosY = PosY - ImgPos[1];
    document.getElementById("x").innerHTML = PosX;
    document.getElementById("y").innerHTML = PosY;
}
