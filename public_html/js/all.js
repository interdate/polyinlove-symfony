var ARENA_USERS = [];
var SHOW_NOW = 0;
var mobileBackgrounds = [
    '/images/mobile-login-background.jpg?v=1',
    '/images/mobile-login-background2.jpg?v=1'
];
var mobileBackgroundNow = 0;
var isSwiped = false;
// var topLazyLoad = false;
var texts = {
    // 'he': {
    //     'success': {
    //         'addToFavorite': 'משתמש זה הוסף לרשימת המועדפים.',
    //         'addToBlackList': 'משתמש זה הוסף לרשימת החסומים.',
    //         'removeFromFavorite': 'משתמש זה הוסר מרשימת המועדפים',
    //         'sendRequestToUser': 'בקשה נשלחה למשתמש',
    //         'removeFromBlackList': 'משתמש זה הוסר מרשימת החסומים'
    //
    //     },
    //     // 'removeFromFavorite': 'הסרה מרשימת המועדפים',
    //     'removeFromBlackList': 'בטל חסימה',
    //     // 'addToFavorite': 'הוסף לרשימת המעודפים',
    //     'addToBlackList': 'חסימה',
    //     'aboutMe': 'קצת עליי',
    //     'sendMessage': 'שלח/י הודעה',
    //     'like': 'לייקֿ',
    //     'arenaNoResult': 'אין תוצאות',
    //     'shortAddToFavorite': 'הוסף למועדפים',
    //     'shortRemoveFromFavorite': 'הסר מהמועדפים',
    //     'addToFavorite': 'הוסף למועדפים',
    //     'removeFromFavorite': 'הסר מהמועדפים',
    //     'thereFor': 'למטרת',
    //     'noPhotos': 'כדי להיכנס לזירה עלייך להעלות תמונה',
    //     'man': 'גבר',
    //     'woman': 'אישה',
    //     'viewProfile': 'לפרופיל המלא',
    //     'noMoreResults': 'אין תוצאות',
    //     'youLiked': 'עשית לייק ל ',
    // },
    'success': {
        'addToFavorite': 'User added to your favorite list.',
        'addToBlackList': 'User added to your black list',
        'removeFromFavorite': 'User removed fro your favorite list',
        'sendRequestToUser': 'Request sent to the user',
        'removeFromBlackList': 'User unblocked'
    },
    // 'removeFromFavorite': 'Remove from favorite',
    'removeFromBlackList': 'Unblock',
    // 'addToFavorite': 'Add to favorite',
    'addToBlackList': 'Block',
    'aboutMe': 'A little about me',
    'sendMessage': 'Send message',
    'like': 'Like',
    'arenaNoResult': 'You dont have a result',
    'shortAddToFavorite': 'Add Favorite',
    'shortRemoveFromFavorite': 'Remove Favorite',
    'addToFavorite': 'Add Favorite',
    'removeFromFavorite': 'Remove Favorite',
    'thereFor': 'For',
    'noPhotos': 'You must upload photo for enter the arena',
    'man': 'Man',
    'woman': 'Woman',
    'viewProfile': 'Full profile',
    'noMoreUsers': 'No more users',
    'youLiked': 'You liked '
    // 'ru': {
    //     'success': {
    //         'addToFavorite': 'Пользователь добавлен в Ваши избранные',
    //         'addToBlackList': 'Пользователь добавлен в Ваш черный список',
    //         'removeFromFavorite': 'Пользователь удален из избранных',
    //         'sendRequestToUser': 'Пользователю отправлен запрос',
    //         'removeFromBlackList': 'Пользователь разлкирован',
    //     },
    //     'removeFromFavorite': 'Удалить из избранных',
    //     'removeFromBlackList': 'Разблокировать',
    //     'addToFavorite': 'Добавить в избранные',
    //     'addToBlackList': 'Заблокировать',
    //     'aboutMe': 'Немного обо мне',
    //     'sendMessage': 'Отправить сообщение',
    //     'like': 'Лайк',
    //     'arenaNoResult': 'Нет результатов',
    //     'shortAddToFavorite': 'Добавить в избранные',
    //     'shortRemoveFromFavorite': 'Удалить из избранных',
    //     'thereFor': 'Для',
    //     'noPhotos': 'Вы должны загрузть фото чтобы зайти на арену',
    //     'man': 'Мужчина',
    //     'woman': 'Женщина',
    //     'viewProfile': 'Полный профиль',
    //     'noMoreResults': 'Больше нет результатов',
    //     'youLiked': 'Вы лайкнуи ',
    // }
};


if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(savePosition);
}


function savePosition(pos) {
    if (pos.coords.latitude && pos.coords.longitude) {
        $.ajax({
            url: '/user/geolocation',
            type: 'POST',
            data: pos,
            success: function (res) {
                console.log(res);
            }
        })
    }
}

function openPopup(id) {
    $('.menu1-login').css({
        'inset-inline-start': '-800px',
    });
    SHOW_NOW = 0;
    //e.preventDefault	();
    id = parseInt(id);
    var addurl = '';
    if (id > 0) {
        addurl = '/' + id;
    }
    $('.username').find('a').attr('href', '/user/users/userID');
    jQuery.ajax({
        url: '/user/like2' + addurl,
        type: "GET",
        dataType: "json",
        success: function (data) {
            ARENA_USERS = data.online;
            // for(var i = 0, i < data.others.length; i++) {
            //
            // }
            console.log(data);
            data.other.forEach(function (elem) {
                ARENA_USERS.push(elem);
            });
            console.log(ARENA_USERS);
            if (data.photos == 0) {
                alert(texts.noPhotos);
                window.location.href = data.url;
            } else if (!ARENA_USERS.length > 0) {
                alert(texts.arenaNoResult)
            } else {
                $('.popup_div .popup-container').find('a').attr('href', $('.popup_div .popup-container').find('a').attr('href').replace('userID', ARENA_USERS[0]['id']))

                console.log($('.username').find('a').attr('href'));
                $('.username span').text(ARENA_USERS[0]['username']);
                $('.userage').text(getAge(ARENA_USERS[0].birthday));
                // ARENA_USERS[0]['id'] = 111;
                // ARENA_USERS[0]['imageId'] = 8;
                // ARENA_USERS[1]['id'] = 3541;
                // ARENA_USERS[1]['imageId'] = 29;
                // ARENA_USERS[2]['id'] = 3548;
                // ARENA_USERS[2]['imageId'] = 58;
                // ARENA_USERS[2]['id'] = 3548;
                // ARENA_USERS[2]['imageId'] = 59;


                console.log(ARENA_USERS);
                console.log(ARENA_USERS[0]['photo']);
                $('.pop_img').css({
                    'background': 'url(' + ARENA_USERS[0]['photo'] + ')' + ' no-repeat',
                    'background-position': 'center',
                    'background-size': '100%',
                });

                // alert($('.popup_ddev'))
                $('.popup_div').css({'display': 'block'});
                $('.pop_title').text()
                $('.fullscreen-container').css({
                    'display': 'block'
                })
            }


            // $('.pop_img').click(function () {
            // 	if (dir == 'left') {
            // 		console.log('left');
            // 		$('.arrow.circle.left.icon').click();
            // 	} else if (dir == 'right') {
            // 		console.log('right');
            // 		$('.arrow.circle.right.icon').click();
            // 	}
            // 	setTimeout(function () {
            // 		dir = 1;
            // 	}, 200);
            // })
        }
    });

    // Bind the swipeHandler callback function to the swipe event on div.box
}


function setIsSwiped() {
    isSwiped = true;
    setTimeout(function () {
        isSwiped = false;
    }, 1000)
}

function getAge(dateString) {
    // alert(1)
    var today = new Date();
    var birthDate = new Date(dateString.replace(' ', 'T'));
    console.log(today.getFullYear())
    console.log(dateString)
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = parseInt(today.getMonth()) - parseInt(birthDate.getMonth());
    console.log(age, m);
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return parseInt(age);
}

function sendLike(id) {
    id = parseInt(id);
    //alert(id);
    if (id > 0) {
        jQuery.ajax({
            url: '/' + '/user/like/' + id,
            type: "POST",
            dataType: "json",
            success: function (data) {
                //alert(data);
                $('.like_click[userid="' + id + '"]').each(function () {
                    //if ($(this).hasClass('nyinner')) {
                    $(this).addClass('inList');
                    //} else {
                    // $(this).remove();
                    //}
                });
                //alert($('.popup.pgal0').css('display') != 'block' && $('.like_click[userid="' + id + '"]').first().size() > 0);
                if ($('.popup.pgal0').css('display') != 'block'
                    && $('.like_click[userid="' + id + '"]').first().size() > 0) {
                    if ($('#alert').size() == 0) {
                        $('body').prepend('<div class="ui modal small" id="alert"><i class="close icon"></i> <div class="header">Notification </div> <div class="content"> <p>Your like was sent successfully.</p> </div> <div class="actions"> <div class="ui button ok">OK</div> </div></div>');
                    } else {
                        $('#alert .content').html('<p>Your like was sent successfully.</p>');
                    }
                    $('#alert.ui.modal').modal({
                        onApprove: function () {
                            $('body').trigger('refresh');
                        }
                    }).modal('show');
                    //alert('Your like was sent successfully.');
                }
            }
        });
    }
}


function signUpFormIsValid() {

    var isError = false;

    $('.field').removeClass('error');

    $('[required="required"]').each(function () {

        if (!$(this).val().length) {
            $(this).parents('.field').addClass('error');
            isError = true;
        }
    });

    if ($('#signUpThree_agree').size() && !$('#signUpThree_agree').is(":checked")) {
        $('#signUpThree_agree').parents('.field').addClass('error');
        isError = true;
    }

    if (isError) {
        $('.errors.empty_fields').show();

        $('html, body').animate({
            scrollTop: $('.errors.empty_fields').offset().top - 130
        }, 1000);

        return false;
    }

    return true;

}


// function checkBingo() {
// 	$.ajax({
// 		url: '/user/bingo',
// 		success: function (res) {
// 			res = JSON.parse(res);
// 			console.log(res);
//
// 			if (res['bingo']) {
// 				// alert('bingo! ' + res.photo1 + ' ' + res.photo2 + ' id: contact - ' + res.contact_id);
//
//
// 				var template = $('#splashBingoTemplate');
// 				$('.overlay').css({'display': 'block'});
// 				console.log($('.chat'));
// 				template.find('.rightPhoto').css({'background-image': 'url(' + res['bingo'].photo1 + ')'});
// 				template.find('.leftPhoto').css({'background-image': 'url(' + res['bingo'].photo2 + ')'});
// 				template.find('.contactId').val(res['bingo'].contact_id);
// 				var newTemplate = template.html().replace('[USERNAME]', res['bingo'].username);
// 				console.log(template.html());
// 				$('#splashBingoTemplate').html(newTemplate);
// 				$('#splashBingoTemplate').css({'display': 'block'});
// 				$.ajax({
// 					url: '/user/bingo/' + res['bingo'].id
// 				});
// 				// } else if (res['privatePhoto']) {
// 				// 	var user = res['privatePhoto'][0]
// 				// 	$('.allowPhotoTempale .username').text(user['username']);
// 				// 	$('.allowPhotoTempale .seeProfile').attr('href', user['href']);
// 				// 	$('.allowPhotoTempale').css({'display': 'block'});
// 				// 	$.ajax({
// 				// 		url: '/user/show/photo/allowed/' + user['requestId']
// 				// 	});
// 				// }
//
// 			}
// 		}
// 	});
//
// 	setTimeout(function () {
// 		checkBingo();
// 	}, 10000)
// }

function closeAllowPhoto() {
    $('.allowPhotoTempale').css({'display': 'none'});
}

function closeBingo() {
    $('#splashBingoTemplate').css({'display': 'none'});
    $('.overlay').css({'display': 'none'});
}

function openDialog() {
    var contactId = $('#splashBingoTemplate').find('.contactId').val();
    var userId = $('#currentUserId').val();
    location.href = '/user/messenger/dialog/open/userId:' + userId + '/contactId:' + contactId;
    closeBingo();
}


function checkLoginState(fromRegistr = false) {
    // alert(1)
    // $.ajax({
    // 	url: '/login/facebook',
    //
    // 	success: function (res) {
    // 		//alert(1);
    // 		console.log('from top: ' + res);
    // 	}
    // });
    FB.getLoginStatus(function (res) {

        if (res.status === 'connected') {
            var fbId = res.authResponse.userID;
            $.ajax({
                url: '/login/facebook',
                type: 'post',
                data: {
                    'fbId': fbId,
                },
                success: function (res) {
                    console.log(res);
                    //alert(1)
                    if (res.text) {

                        $('#usernameLogin').attr('data-id', fbId);
                        $('.facebookLoginPop').modal('show');
                        $('.registration').attr('href', '/sign_up/' + fbId);
                        $('.facebookLoginId').val(fbId);
                        if (fromRegistr) {
                            FB.api('/me', {fields: 'email'}, function (response) {
                                if (response.email) localStorage.setItem('facebookEmail', response.email);
                                location.href = '/sign_up/' + response.id;
                            });
                        }
                    } else {
                        location.reload();
                    }
                }
            });
        } else {
            FB.login(function (res) {
                console.log(res);
                if (res.status == 'connected') {
                    checkLoginState();
                }
            }, {scope: 'email'});
        }
    }, {scope: 'email'});
}


function favAndBlackListActionsInit() { //and delete message
    console.log('binding');
    $('.add_to_fav, .ask_photo, .add_to_back_list, .delete_from_fav, .delete_from_black_list').unbind('click').bind('click', function (e) {
        e.preventDefault();
        console.log('firing');
        //var action = ($(this).hasClass('add_to_fav')) ? 'favorite' : 'black_list';

        var refreshPage = false;

        if ($(this).hasClass('add_to_fav')) {
            var action = 'favorite';
            var successMessage = texts.success.addToFavorite;
        } else if ($(this).hasClass('add_to_back_list')) {
            var action = 'black_list';
            var successMessage = texts.success.addToBlackList;
        } else if ($(this).hasClass('delete_from_fav')) {
            var action = 'favorite/delete';
            var successMessage = texts.success.removeFromFavorite;
            //refreshPage = true;
        } else if ($(this).hasClass('delete_from_black_list')) {
            var action = 'black_list/delete';
            var successMessage = texts.success.removeFromBlackList;
            //	refreshPage = true;
        } else if ($(this).hasClass('ask_photo')) {

            var action = 'ask_photo';
            var successMessage = texts.success.sendRequestToUser;
            refreshPage = true;
        }

        var id = $(this).attr('data-id');

        if (!id) {

            id = $('.isMobile').val() ? $(this).parents('.user-section').find('.addLike').attr('data-id') : $(this).parents('.boxcont').find('.userId').val();
        }
        var fullProf = $(this).attr('data-full-profile') === 'true';
        listAction(action, id, refreshPage, successMessage, fullProf, $(this));
    });


    $('.delete-message-link').click(function () {

        var userId = $('#currentUserId').val();
        var contactId = $('#contactId').val();
        var elem = $(this);
        var id = elem.attr('data-id');
        var deleteFrom = elem.parents('.messageSection').hasClass('user');
        console.log(deleteFrom);
        $.ajax({
            url: '/messenger/message/delete/userId:' + userId + '/' + id + '/' + contactId + '/' + deleteFrom,
            headers: {'apiKey': Messenger.apiKey},
            success: function (res) {
                if (res) {
                    console.log(elem.parents('.messageSection'));
                    elem.parents('.messageSection').remove();
                }
            }
        });
    });

    $('.user-section .avatar, .user-section .mobmsg, .boxcont .greyboxcont .f1, .boxcont .user-image').click(function (e) { // mobile /he/user/
        e.preventDefault();
        var userSection = $('.isMobile').val() ? $(this).parents('.user-section') : $(this).parents('.boxcont');
        var url = $(this).attr('data-href') ?? $(this).attr('href');
        var id = userSection.attr('id');
        if (!$('.currentUserId').val()) {
            url = '/sign_up';
        }
        if (!url) {
            url = userSection.find('a').attr('href');
        }

        document.cookie = 'userId=' + id + '; path=/';
        window.history.replaceState(null, null, $('.requestUrl').val() + '/' + userSection.attr('data-page') + '/0');
        window.location.href = url;

    });
}


function loginFacebookWithExistAccount() {
    $('.facebookLoginPop').modal('hide');
    $('.first-mobile-login-btn').click();
    if ($('#login_form').parent('.form').css('display') == 'none') {
        $('.logbtn').click();
    }
    alert('Please fill in your login credentials (one time only)')
}


function deleteMessage(elem) {
    var contactId = $('#contactId').val();
    var elem = $(elem);
    var id = elem.attr('data-id');
    var deleteFrom = elem.parents('.messageSection').hasClass('user');
    console.log(deleteFrom);
    $.ajax({
        url: '/messenger/message/delete/' + id + '/' + contactId + '/' + deleteFrom,
        success: function (res) {
            if (res) {
                console.log(elem.parents('.messageSection'));
                elem.parents('.messageSection').remove();
            }
        }
    });
}


// window.onload = function() {
// 	// alert('test')
//
// }
function initUsersLists() {
    console.log('on load from all.js')
    setTimeout(function () {
        console.log('scroll')
        if (readCookie('userId') && $('.requestUrl').size()) {
            // alert(3);

            // alert('test')
            var hash = $('#' + readCookie('userId'));
            // alert(hash.offset().top)
            if (hash.offset()) {


                window.scrollTo(0, hash.offset().top - 200);

                delete_cookie('userId')


            }
        }
    }, 500)
}

jQuery(document).ready(function ($) {

    $('.loginError').delay(2400).transition({name:'fade', duration:'1200ms'});

    // $('.loginError').hide(2400);

    // setTimeout(function () {

    // }, 2000)
    var params = new URL(window.location.href).searchParams;

    if (params.get('arena')) {
        openPopup();
    }

    var isActive;

    $.ajax({
        url: '/user/bingo',
        success: function (res) {
            //console.log(res);
            if (res.slice(0, 2) == '{"') {
                res = JSON.parse(res);
                console.log(res);

                if (res['bingo']) {


                    var template = $('#splashBingoTemplate');
                    $('.overlay').css({'display': 'block'});
                    console.log($('.chat'));
                    template.find('.rightPhoto').css({'background-image': 'url(' + res['bingo'].photo1 + ')'});
                    template.find('.leftPhoto').css({'background-image': 'url(' + res['bingo'].photo2 + ')'});
                    template.find('.contactId').val(res['bingo'].contact_id);
                    var newTemplate = template.html().replace('[USERNAME]', res['bingo'].username);
                    console.log(template.html());
                    $('#splashBingoTemplate').html(newTemplate);
                    $('#splashBingoTemplate').css({'display': 'block'});
                    $.ajax({
                        url: '/user/bingo/' + res['bingo'].id
                    });


                }
            }
        }
    });


    var myElement = document.getElementsByClassName('pop_img')[0];
    if (myElement) {
        var mc = new Hammer(myElement);
        var dir = '';

        // if(!$('.isMobile').val()) {
        // 	$(myElement).click(function () {
        // 		if(!isSwiped) {
        // 			// alert(1)
        // 			$('.arrow.circle.right.icon').click();
        // 		}
        // 	});
        // }
        //
        mc.on("swipeleft", function (ev) {
            console.log('swipeleft')
            setIsSwiped();
            $('.arrow.circle.left.icon').click();
        });

        mc.on("swiperight", function () {
            console.log('swiperight')
            setIsSwiped();
            $('.arrow.circle.right.icon').click();
        });
    }


    getDataFromServer()
    $(window).focus(function () {
        isActive = true;
    });

    $(window).blur(function () {
        isActive = false;
    });

    // checkBingo();
    favAndBlackListActionsInit();

    if (localStorage.getItem('uploadImage')) {

        $.ajax({
            url: '/user/photo/data/add/' + localStorage.getItem('uploadImage'),
            success: function () {
                localStorage.removeItem('uploadImage');
            }
        })
    }


    if (localStorage.getItem('facebookEmail')) {
        $('#sign_up_one_email').val(localStorage.getItem('facebookEmail'));
        localStorage.removeItem('facebookEmail');
    }

    $('.topbtns-lang').click(function (e) {
        e.preventDefault()
    });
//	alert(2);
    $('.addVerify').click(function () {
        var id = $(this).attr('data-id');
        var elem = $(this);
        console.log(elem);
        $.ajax({
            url: '/user/verify/' + id,
            success: function (res) {
                console.log(elem);
                // if(res) {
                alert('Thank you for verifying profile');
                console.log(123);
                elem.parents('li').remove();
                elem.addClass('opacity-05');
                elem.remove();
                $('.addVerify').remove();

                // }
            }
        });

    });
    //used to initialize tabs from the semantic ui library. used on nontifications page, maybe elsewhere as well
    if ($('.demo.menu .item').length > 0) {
        $('.demo.menu .item').tab({
            history: false
        });
    }
    // $('.registrPhotoInput').change(function (e) {
    // //	//alert(11);
    // 	e.preventDefault();
    // 	console.log($('.registrPhotoInput').prop('files')[0]);
    // 	var formData = new FormData();
    //
    // 	formData.append("photo", $('.registrPhotoInput').prop('files')[0]);
    // 	$.ajax({
    // 		url: '/user/photo/data',
    // 		type: 'post',
    // 		data: formData,
    // 		cache: false,
    // 		contentType: false,
    // 		processData: false,
    //
    // 		success: function (res) {
    // 			location.href = '/user/profile/4'
    // 		}
    //
    // 	});
    // });

    $('.pop_con a').click(function (e) {
        e.preventDefault();
        arenaClick(this);
        return false;
    });
    popslide = jQuery('.popup.pgal0 .popslider').lightSlider();


    $('.arrow.circle.right.icon').click(function () {
        console.log('right');
        if (SHOW_NOW == ARENA_USERS.length - 1) {
            // alert('אין יותר משתמשים. חוזר למשתמש הראשון');
            var willShow = 0;
        } else {
            var willShow = SHOW_NOW + 1;
        }
        if (ARENA_USERS.length - 1 >= 0) {
            $('.username span').text(ARENA_USERS[willShow]['username']);
            $('.userage').text(getAge(ARENA_USERS[willShow].birthday));
            $('.pop_img').css({
                'background': 'url(' + ARENA_USERS[willShow]['photo'] + ')' + 'no-repeat'
            });

            $('.popup_div .popup-container').find('a').attr('href', $('.popup_div .popup-container').find('a').attr('href')
                .replace(ARENA_USERS[SHOW_NOW]['id'], ARENA_USERS[willShow]['id']));

            $('.popup_div .popup-container').find('a').attr('href', $('.popup_div .popup-container').find('a').attr('href')
                .replace('userID', ARENA_USERS[willShow]['id']));

            // ARENA_USERS.splice(SHOW_NOW, 1);
            SHOW_NOW = willShow;
        } else {
            alert(texts[LCOALE].noMoreUsers);
            $('.fullscreen-container').click();
        }
        console.log(SHOW_NOW);
    });

    $('.arrow.circle.left.icon').click(function () {
        // alert(4);
        console.log('left')
        var willShow = SHOW_NOW == 0 ? ARENA_USERS.length - 1 : SHOW_NOW - 1;

        $('.popup_div .popup-container').find('a').attr('href', $('.popup_div .popup-container').find('a').attr('href')
            .replace(ARENA_USERS[SHOW_NOW]['id'], ARENA_USERS[willShow]['id']));
        $('.username span').text(ARENA_USERS[willShow]['username']);
        $('.userage').text(getAge(ARENA_USERS[willShow].birthday));
        console.log(ARENA_USERS[willShow])
        $('.pop_img').css({
            'background': 'url(' + ARENA_USERS[willShow]['photo'] + ')' + 'no-repeat'
        });
        SHOW_NOW = willShow;
        console.log(SHOW_NOW);
    });


    $("input").focusin(function () {
        $('.banner-app-download, .spedate-fixed').hide();
    });

    $("input").focusout(function () {
        $('.banner-app-download, .spedate-fixed').show();
    });

    $(".banner-app-download .close-banner").on('click', function () {
        $.ajax({
            url: '/close_app_notification'
        });
        var bot = ($('.banner-app-download').hasClass('logged')) ? '60px' : '10px';
        $('.banner-app-download').remove();
        $('body .userway.userway_p5').css({'bottom': bot});
    });

    $(window).load(function () {
        var userwayInterval = setInterval(function () {
            if ($('.banner-app-download').size() > 0 && $('body .userway.userway_p5').size() > 0 && $('.banner-app-download').css('display') == 'block') {
                var bot = ($('.banner-app-download').hasClass('logged')) ? '140px' : '90px';
                $('body .userway.userway_p5').css({'bottom': bot});
                clearInterval(userwayInterval);
            } else {
                if ($('.banner-app-download').size() == 0 && $('.bottom_menu').css('display') != 'none') {
                    $('body .userway.userway_p5').css({'bottom': '60px'});
                    clearInterval(userwayInterval);
                }
            }
        }, 50);
    });

    //== form toggle
    $('.logbtn').click(function (e) {
        e.preventDefault();
        $('header .form').fadeToggle('fast');
    });

    $("#menu a").removeAttr("title");
    $(".menu-item-has-children").hover(function () {
        $(this).children("ul.sub-menu").slideToggle(100)
    });

    $(".tglmenu").click(function (e) {
        e.preventDefault();
        $("#menu").toggle();//slideToggle(200)
    });

    $(".overlay").click(function (e) {
        e.preventDefault();
        // $("#menu").toggle();
        $(".tglmenu").click()
    });

    if ($(window).width() <= 640) {
        //$('.headerinn').addClass('scroll');
    }
    $(window).on("resize", function () {
        if ($(window).width() > 1000 && $("#menu").is(":hidden")) {
            $("#menu").removeAttr("style")
        }
    }).trigger("resize");

    $(window).scroll(function () {
        $(".scroltop").toggleClass("showscroll", $(document).scrollTop() >= 200);
        //$("header").toggleClass("opac", $(document).scrollTop() >= 100);
        //$('.headerinn').toggleClass('scroll', $(document).scrollTop() >= 20);
    }),

        $(".scroltop").click(function (e) {
            e.preventDefault();
            $("html,body").animate({scrollTop: 0}, 300);
        });

    //== TABS
    $('.tabs').hide();
    $('.tabnav a').bind('click', function (e) {
        $('.tabnav a.active').removeClass('active');
        $('.tabs:visible').hide();
        $(this.hash).fadeIn(200);
        $(this).addClass('active');
        e.preventDefault();
        //}).filter(':nth(1)').click();
    }).filter(':first').click();

    //== faq
    $(".faqtitle").click(function (e) {
        e.preventDefault();
        //$(this).closest('.forumfaq').find('.active').removeClass('active');
        $(this).toggleClass('active');
        //$(".fcont").slideUp();
        $(this).next(".fcont").slideToggle(500);
    });
    //$(".faqtitle:first").click();
    $(".faqtitle:first").addClass('active');

    //== slider
    $('.hmslider').slick({
        arrows: false,
        dots: true,
        autoplay: true,
        rtl: false,
        speed: 1000,
    });

    //== Popup
    //
    //    $('.add_to_fav').addEventListener('click', function (e) {
    //        //alert(1)
    //        e.preventDefault();
    //    });

    $('#user_data').css({'max-height': ($(window).height() * 0.8)})

    // $('.f1').bind('click', function(e){
    //
    //    console.log($(this).offset().top);
    //
    // 	e.preventDefault();
    // 	$('#user_data').html('');
    // 	$('#profile_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    // 	$(this.hash).fadeIn(300);
    // 	$('.overlay').fadeIn(300);
    //
    //    var userId = ( $(this).parents('.boxcont').size() )
    //        ? $(this).parents('.boxcont').find('.userId').val()
    //        : $(this).siblings('input[type="hidden"]').val()
    //    ;
    //
    //
    //    var marTop = $(this).offset().top - 400;
    //    if(marTop < 0){
    //        marTop = 0;
    //    }
    //
    //    $('.popupmainopen').css('margin-top', marTop);
    //
    // 	getUserData(userId);
    // });

    $('.inlineMess').click(function () {
        // $('.inlinemessages').css({
        // 	'display': 'block'
        // });
        // alert(1);
        $('.inlinemessages').find('.checked').removeClass('checked');
        if ($('.inlinemessages').css('display') == 'none') {
            $('.inlinemessages').fadeIn(200);
        } else {
            $('.inlinemessages').fadeOut(200);
        }
    });


    $('.users, .profile-buttons').on('click', '.addLike', function (e) {
        // alert(24)
        e.preventDefault();
        var elem = $(this);
        var id = $(this).attr('data-id');
        $.ajax({
            url: '/user/like/' + id,
            success: function (res) {
                var name = elem.attr('data-name') ? elem.attr('data-name') : elem.attr('data-username')
                alert('עשית לייק ל' + name);
                if ($('.isMobile').val()) {
                    elem.attr('disabled', true);
                    elem.addClass('a-disabled');
                } else {
                    elem.parent('li').remove();
                    elem.remove();
                }

            }
        });
    });

    $('.btns .addLike, .profile-nav .addLike').click(function (e) {
        //alert(123);
        e.preventDefault();
        console.log($(this));
        var elem = $(this);
        var id = $(this).attr('data-id');
        $.ajax({
            url: '/user/like/' + id,
            success: function (res) {
                alert(texts.youLiked + elem.attr('data-username'));
                // elem.parent('li').remove();
                // elem.remove();
                // if (elem.attr('data-full-profile')) {
                $('.addLike').parent('li').remove();
                $('.addLike').remove();
                // }
            }
        });
    });


    $('.photo-home-page-checkbox').click(function (e) {

        $.ajax({
            url: '/user/photos/homepage',
            success: function (res) {
                //
            }
        })
    });


    $('.inlineMessItem').dblclick(function () {
        //console.log($(this).val());
        var id = $(this).attr("data-id");
        //console.log($(this).text());
        sendInstantMessage(id, $(this));
    });

    $('.inlineMessItem').click(function () {
        $('.inlineMessItem').removeClass('checked');
        $(this).addClass('checked');
    });

    $('.inlinemessages .sendbtn').click(function () {
        var id = $(this).parents('ul').find('.checked').attr('data-id');
        sendInstantMessage(id, $(this).parents('ul').find('.checked'));
    });


    // $('.view_my_profile').click(function(e){
    // 	e.preventDefault();
    // 	$('#user_data').html('');
    // 	$('#profile_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
    // 	$(this.hash).fadeIn(300);
    // 	$('.overlay').fadeIn(300);
    // 	$('.popupmainopen').css('margin-top', 0);
    // 	getUserData($('#my_user_id').val());
    // });


    $('.close').bind('click', function (e) {
        e.preventDefault();
        $(this).parents('.popupmainopen').fadeOut(300);
        $('.fullscreen-container').css({
            'display': 'none'
        });
        $('.overlay').fadeOut(300);
        $('.popup_div').fadeOut(333);
    });
    $('.fullscreen-container').click(function (e) {
        $('.popupmainopen, .overlay').fadeOut(300);
        // $('.fullscreen-container').css({
        // 	'display': 'none'
        // });
    });

    // Checkbox
    $('.css-label').click(function () {
        $(this).closest('.chkbox').toggleClass('act');
    });


    //**********************************************

    // List
    $(".tgllists").click(function () {
        $(".tgllists").toggleClass('tglactive');
        $("#tgllistsbox").slideToggle(300);
    });
    $(window).on('resize', function () {
        if ($(window).width() > 801 && $('#tgllistsbox').is(':hidden')) {
            $('#tgllistsbox').removeAttr('style');
        }
    });

    // Account Management
    $(".tglacc-mng").click(function () {
        $(".tglacc-mng").toggleClass('tglactive');
        $("#tglacc-mngbox").slideToggle(300);
    });
    $(window).on('resize', function () {
        if ($(window).width() > 801 && $('#tglacc-mngbox').is(':hidden')) {
            $('#tglacc-mngbox').removeAttr('style');
        }
        //$('.scroltop').css({'right':  $(window).width()/2 - 635 + 'px' });
    }).trigger('resize');
    // Account Management


    //== chkbox
    /*$('.chkbox').click(function () {
     $(this).addClass("active");
     $(this).parent().addClass("active");
     if ($(this).find('input:checkbox').is(":checked")) {
     $(this).find('input:checkbox').attr("checked", false);
     $(this).parent().removeClass("active");
     $(this).removeClass("active"); }
     else {
     $(this).find('input:checkbox').prop("checked", true);
     }
     });*/


    $('.faqs dd').hide(); // Hide all DDs inside .faqs
    $('.faqs dt').hover(function () {
        $(this).addClass('hover')
    }, function () {
        $(this).removeClass('hover')
    }).click(function () { // Add class "hover" on dt when hover
        $(this).next().slideToggle('normal'); // Toggle dd when the respective dt is clicked
    });

    $(".toggleh3").click(function () {
        $(this).toggleClass('deactive');
        $(".toggletext").slideToggle(500);
    });

    var showCantWriteAlert = false;
    $('.message_area, .inlineMess.dialog, #dialog_textarea, .inlineMess').click(function () {
        var elem = $(this);

        $.ajax({
            url: '/user/check/contact/' + $('#contactId').val(),
            success: function (res) {
                if (!res.canContact) {

                    if (!showCantWriteAlert) {
                        alert('Due to this users settings, you may not message them');
                        showCantWriteAlert = true;
                    }
                    elem.parents('.send_message_area').find('textarea').attr('disabled', true) // for mobile
                    $('#dialog_textarea').attr('disabled', true) //for desktop
                } else {
                    if (!res.canCreateNewChat) {
                        alert('You may not send any more messages today. Please upload a photo to be allowed to send more messages.');
                        elem.parents('.send_message_area').find('textarea').attr('disabled', true) // for mobile
                        $('#dialog_textarea').attr('disabled', true) //for desktop
                    }
                }
            }
        })
    });
    /******************** User Photos And Cloudinary *********************************************/

    // $.cloudinary.config({ cloud_name: 'interdate', api_key: '771234826869846'});
    $('.ui.progress').hide();
    //$('#global_dimmer').hide();

    // if($('#top_thumb').size()){
    //
    //    var topThumb = $('#top_thumb').val();
    //    var currentUserGender = $('#current_user_gender').val();
    //
    // 	var url = (topThumb.length)
    // 		? $.cloudinary.url(topThumb, { width: 23, height: 23, crop: 'thumb', gravity: 'face', format: 'png', radius: 3 })
    // 		: $('#no_photo_url_' +  currentUserGender).val()
    //    ;
    //
    //    $('#top_thumb').parents('a').find('img').attr('src',url);
    //
    // }


    $('.cloudinary-fileupload').bind('fileuploadstart', function (e, data) {
        $('.ui.progress').show();
        $('.browsebutt, .browseinput').attr("disabled", "disabled");
    });

    $('.cloudinary-fileupload').bind('fileuploadprogress', function (e, data) {
        var value = Math.round((data.loaded * 100.0) / data.total);
        $('.ui.progress').progress({
            percent: value,
        });
        $('#upload_photo_label span').text(value);
    });

    $('.cloudinary-fileupload').bind('cloudinarydone', function (e, data) {
        //console.log(JSON.stringify(data));
        $('.browsebutt, .browseinput').removeAttr("disabled");

        $('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
        //return;
        $('.ui.progress').hide();
        savePhotoData(data);
        return true;
    });

    if ($('.photoName').size()) {
        $('.photoName').each(function (index) {
            var template = $('#photo_template').html();
            var idArr = $(this).val().split("__");
            var photoName = idArr[0];
            var photoId = idArr[1];
            var main = idArr[2] == 1 ? 'checked="checked"' : '';
            var status = idArr[3] == 1 ? 'approved' : 'awaiting approval';
            var private = idArr[4] == 1 ? 'checked="checked"' : '';
            var privateDisable = main ? 'disabled=disabled' : '';
            var mainDisable = private ? 'disabled=disabled' : '';
            console.log(private);
            var node = template.replace(/\[PHOTO_NAME\]/, photoName);
            node = node.replace(/\[PHOTO_ID\]/g, photoId);
            node = node.replace(/\[main\]/, main);
            node = node.replace(/\[STATUS\]/, status);
            node = node.replace(/\[private\]/, private);
            node = node.replace(/\[private_disabled\]/, privateDisable);
            node = node.replace(/\[main_disabled\]/, mainDisable);
            console.log(node);
            $('.photos').append(node);
            // $('#' + photoName).find('.imgbox').html(
            // 	$.cloudinary.image(photoName + '.jpg',{
            // 		crop: 'fill',
            // 		width: 210,
            // 		height: 260
            // 	})
            // );
        });

        $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");

    }

    // $('.cloudinaryForm input[type="file"]').change(function(){
    // 	$('.browseinput').val($(this).val());
    // });


//	//alert(1);
    $('.photoInput').change(function () {
        ////alert(1);
        //  $(this).closest('form').find('input[type="submit"]').first().click();
        //$('.submitPhoto').click();
        //$('.registrPhotoInput').change()
    });


    $('.removePhoto').click(function (e) {
        e.preventDefault();
        var id = $(this).attr("id");
        var node = $(this).parents('.photo');

        $('.ui.basic.modal')
            .modal({
                closable: true,
                onApprove: function () {
                    deletePhoto(id, node);
                }
            })
            .modal('show')
        ;
    });


    // $('.privatePhoto').click(function() {
    // 	console.log($(this));
    // 	setPrivatePhoto($(this));
    // });


    $('.sidebarPhoto').each(function () {

        if ($(this).hasClass('sidebarPhoto')) {
            var width = 128;
            var height = 154;
        } else {
            var width = 184;
            var height = 218;
        }

        var photoName = $(this).siblings('input[type="hidden"]').val();

        var genderId = $(this).parents('.boxcont').find('.userGenderId').val();
        console.log(genderId);

        var url = photoName;


        // $(this).attr('src',url)
        //.css({"width":width, "height": height})
        ;
    });


    /******************** Articles Cloudinary *********************************************/
    //
    // if($('.magcont').size() || $('.hp-articles').size()){
    // 	if($('.previewImageName').size()){
    // 		$('.previewImageName').each(function(){
    // 			var url = $.cloudinary.url($(this).val(), { width: 184, height: 218, crop: 'fill' });
    // 			// $(this).siblings('img').attr('src',url);
    // 		});
    // 	}
    //
    // 	if($('.imageName').size()){
    // 		var url = $.cloudinary.url($('.imageName').val(), { crop: 'fill' });
    // 		// $('.imageName').siblings('img').attr('src',url);
    // 	}
    //
    //
    // 	if($('.homepageImageName').size()){
    // 		$('.homepageImageName').each(function(){
    // 			var url = $.cloudinary.url($(this).val(), { width: 176, height: 176, crop: 'fill' });
    // 			// $(this).siblings('img').attr('src',url);
    // 		});
    // 	}
    // }

    /******************** Home Page Cloudinary *********************************************/

    // if($('.slides').size()){
    //
    // 	$('.imageName').each(function(){
    // 		var url = $.cloudinary.url($(this).val(), {format: 'jpg'});
    // 		$(this).parents('.slide').css('background', 'url(' + url + ') no-repeat top center');
    // 	});
    //
    // }
    //
    //
    // $('.faceImageName').each(function(){
    // 	var url = $.cloudinary.url($(this).val(), { width: 200, height: 200, crop: 'thumb', gravity: 'face', radius: 'max', format: 'png' })
    // 	//console.log(url);
    // 	$(this).siblings('.url').val(url);
    // });
    //
    //
    // /******************** Messages Page Cloudinary *********************************************/
    //
    // if($('.hotlist').size()){
    //
    // 	$('.hotlist .userimg input[type="hidden"]').each(function(){
    // 		var url = $.cloudinary.url($(this).val(), { width: 86, height: 86, crop: 'thumb', gravity: 'face', format: 'png' })
    // 		//console.log(url);
    // 		// $(this).parent().find('img').attr('src',url);
    // 	});
    //
    // }


    /**************************************************************************/

    // $('.errors ul').addClass("list");
    //
    // $('.qs .free').click(function(e){
    // 	e.preventDefault();
    // 	$(this).siblings('input[type="submit"]').click();
    // });
    //

    $('#advancedSearch_withPhoto').click(function () {
        var filter = $(this).is(':checked') ? 'photo' : $('#search_filter_by_default').val();
        $('#advancedSearch_filter').val(filter);
    });

    var selectedFilter = $('#advancedSearch_filter').val($(this).val());
    $('#searchFilter').change(function () {
        document.cookie = 'page=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        // $.removeCookie(selectedFilter);
        $('#advancedSearch_filter').val($(this).val());
        // $.cookie('test', 123123123);
        $('#search_filter_form')
            // change page number to 1 in url when switching filter
            .attr('action', $('#search_url_when_switching_filter').val())
            .find('input[type="submit"]')
            .click()
        ;
    });

    $('.usersResults .first a, .usersResults .previous a, .usersResults .page a, .usersResults .next a, .usersResults .last a').click(function (e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $('#search_filter_form')
            .attr('action', url)
            .find('input[type="submit"]')
            .click()
        ;
    });

    // $('.rptimgbox.boxcont').popup();

    $('.tgl_qs').click(function () {
        $(this).toggleClass('open')
        if ($(this).hasClass('open')) {
            console.log(3);
            $(this).find('.white-circle').text('-');
        } else {
            $(this).find('.white-circle').text('+');
        }
        $('#quick_search_sidebar_form').toggle();
    });

    $('.tgl_mqs').click(function () {
        $(this).toggleClass('open');
        $('.advance-search-btn').toggleClass('hidden');
        if ($(this).hasClass('open')) {
            console.log(3);
            $(this).find('.white-circle').text('-');
        } else {
            $(this).find('.white-circle').text('+');
        }
        $('.left_sep').toggleClass('hidden');
        $('.qs').toggle();
    });

    $('.nextstage').click(function (e) {
        e.preventDefault();
        if (signUpFormIsValid()) {
            $('#next_stage').click();
        }
    });

    $('.contact_submit').click(function (e) {
        e.preventDefault();
        if (contactFormIsValid()) {
            $('#send').click();
        }
    });
    /*

     $('.bottom_menu .item').bind('touchstart', function(){
     $(this).addClass('active');
     });

     $('.bottom_menu .item').bind('touchend', function(){
     $(this).removeClass('active');
     })

     */


    $('.localeChange').change(function (e) {
        e.preventDefault();
        console.log($('.localeChange').val());
        LOCALE = $('.localeChange').val()
        location.href = '/' + LOCALE;
    })

    $('.qs .free').click(function (e) {
        e.preventDefault();
        $(this).siblings('input[type="submit"]').click();
    });

    $('.tglmenu').click(function () {
        if ($('.menu1-login').css('inset-inline-start') == '-800px') {
            $('.menu1-login').css({'inset-inline-start': '0px'});
            $('.main-container').css({'overflow': 'hidden'});
            $('.overlay').css({'display': 'block'})
        } else {
            $('.menu1-login').css({'inset-inline-start': '-800px'});
            $('.menu2-settings').css({'inset-inline-start': '-800px'});
            $('.menu3-lists').css({'inset-inline-start': '-800px'});
            $('.main-container').css({'overflow': 'auto'});
            $('.overlay').css({'display': 'none'});
        }
    });

    $('.menu-settings-trigger').click(function () {

        $('.menu2-settings').css({'inset-inline-start': '0px'});
    });

    $('.menu-back-button').click(function () {
        $('.menu2-settings').css({'inset-inline-start': '-800px'});
        $('.menu3-lists').css({'inset-inline-start': '-800px'});
    });

    $('.menu-list-trigger').click(function () {
        $('.menu3-lists').css({'inset-inline-start': '0px'});
    });

    console.log('attaching')
    $('.first-mobile-login-btn').click(function (e) {
        // $(this).addClass('hidden');
        $(this).parents('.first-login-mobile-btns').addClass('hidden');
        // $('.facebook-mobile-login-btn').addClass('hidden');
        $('.login-container-background').css({'display': 'block'});
        setTimeout(function () {
            $('.login-container-background').find('.txtbox').focusout();
        }, 300)
    });

    $('.swiper1').click(function () {
        $('.mobile-menu1').find('ul').css({'inset-inline-start': '-800px'});
        $('.mobile-menu2').find('ul').css({'inset-inline-end': '0'});
        $('.swiper1').css({'display': 'none'});
        $('.swiper2').css({'display': 'inline-block'});
    });

    $('.swiper2').click(function () {
        $('.mobile-menu2').find('ul').css({'inset-inline-end': '-800px'});
        $('.mobile-menu1').find('ul').css({'inset-inline-start': '-0'});
        $('.swiper2').css({'display': 'none'});
        $('.swiper1').css({'display': 'inline-block'});
    });

    $('.sorting .close').click(function () {
        $('.down.icon.angle').css({'display': 'block'})
        $('.member.cf.sorting').css({'display': 'none'})
    });

    $('.down.icon.angle').click(function () {
        $('.down.icon.angle').css({'display': 'none'})
        $('.member.cf.sorting').css({'display': 'block'})
    });

    $('.bottom-near-me').click(function () {
        location.href = '/user?filter=distance';
        // $('#searchFilter').val('distance');
        // $('#advancedSearch_filter').val('distance');
        //
        // $('#search_filter_form').submit();
    });

    // $('.close-arena').click(function () {
    // 	$('.popup_div').css({'display': 'none'});
    // 	$('.fullscreen-container').css({'display': 'none'})
    // });


    $('.fullscreen-container').click(function () {
        $(this).css({'display': 'none'});
        $('.popup_div').css({'display': 'none'});
        $('.user-images').removeClass('active');
    })

    $('#sign_up_one_username').focusout(function () {
        var elem = $(this);
        if ($(this).val().length > 0) {
            $.ajax({
                url: '/name-exist/' + $(this).val(),
                success: function (res) {
                    if (res == 1) {
                        // this username exist
                        console.log($(this).parents('.field'));
                        elem.parents('.field').addClass('error');
                        $('.username-error').css({'display': 'block'})
                        $('.username-free').css({'display': 'none'});

                    } else {
                        elem.parents(('.field')).removeClass('error');
                        $('.username-error').css({'display': 'none'});
                        elem.addClass('border-green');
                        $('.username-free').css({'display': 'block'});

                    }
                }
            })
        }
    });

    if ($('.contact-success')) {
        $('#contact_subject, #contact_text').keyup(function () {
            $('.contact-success').addClass('hidden')
        })
    }


    // $('.delete-message-link').click(function () {
    // 	var contactId = $('#contactId').val();
    // 	var elem = $(this);
    // 	var id = elem.attr('data-id');
    // 	var deleteFrom = elem.hasClass('user');
    // 	$.ajax({
    // 		url: '/messenger/message/delete/' + id + '/' + contactId + '/' + deleteFrom,
    // 		success: function (res) {
    // 			if(res) {
    // 				elem.remove();
    // 			}
    // 		}
    // 	});
    // });

    // $('#sign_up_form').submit(function (e) {
    // 	e.preventDefault();
    // 	$(this).find('.nextstage').click();
    // });

    $('.readAll').click(function () {
        var action = $(this).parent('div').find('a.active').attr('data-tab');
        $.ajax({
            url: '/user/notifications/' + action,
            success: function () {
                $('div[data-tab="' + action + '"]').find('div.boxrpt').addClass('readNotif');
            }
        })
    });

    $('.notification, .bingo .bingo').click(function () {
        var elem = $(this);
        if (!elem.hasClass('readNotif')) {
            $.ajax({
                url: '/user/notifications/' + elem.attr('notification-id'),
                success: function (res) {
                    elem.addClass('readNotif');
                }
            })
        }
        if (elem.hasClass('bingo')) {
            console.log(elem)
            location.href = elem.find('.bingo-href').val();
        }

    });

    setTimeout(function () {
        changeMobileBackground()
    }, 6000);

    $('.mobile-login-form .txtbox').focusin(function () {
        $(this).parents('.login-container-background').css({'margin-bottom': '50px'})
    });

    $('.mobile-login-form .txtbox').focusout(function () {
        var elem = $(this);
        setTimeout(function () {
            elem.parents('.login-container-background').css({'margin-bottom': 'auto'})
        }, 300);

    });

    $('.report-close').click(function () {
        $(this).parent().css({'display': 'none'});
    });

    $('.banner').click(function (e) {
        e.preventDefault();
        var elem = $(this);
        $.ajax({

            url: '/banner/' + $(this).attr('data-id') + '/count',
            // url: '/app_dev.php/he/banner/36/count',
            success: function () {
                //console.log(elem.parents('a').attr('href'));
                window.open(elem.parents('a').attr('href'), '_blank');
            }
        })
    });

    $('.photo-request-btn.allow').click(function () {
        var userId = $('.currentUserId').val();
        var contactId = $(this).attr('data-id');
        var requestId = $(this).parent('.desctext').find('.requestId').val();
        console.log($(this));
        $.ajax({
            url: '/messenger/message/send/userId:' + userId + '/contactId:' + contactId,
            headers: {'apiKey': Messenger.apiKey},
            timeout: 80000,
            dataType: 'json',
            type: 'Post',
            data: 'message=' + 'QUICKMESSALLOW' + "&tag=LI",
            context: this,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            error: function (response) {
                console.log(JSON.stringify(response));
            },
            success: function (response, status) {
                if (response.success) {
                    console.log('MESSAGE:' + JSON.stringify(response.message));
                    console.log('END SENDING');

                    location.href = '/user/users/photo/requests/' + contactId + '/allow';
                }
            }

        });
    });

    if ($('.isMobile').val()) {
        var bottom_menu_slide1 = document.querySelector('.mobile-bottom-menu');

        var hamm = new Hammer(bottom_menu_slide1);
        hamm.on('swipe', function () {
            if ($('.swiper1').css('display') === 'none') {
                $('.swiper2').click();
            } else {
                $('.swiper1').click();
            }
        });
    }

    $('.delete-message-icon').click(function (event) {
        event.stopPropagation();
        var ionCart = $(this).parents('.ion-cart');
        var boxrpt = $(this).parents('.boxrpt');
        var isMobile = $('.isMobile').val();
        var id = isMobile ? ionCart.find('.userId').val() : boxrpt.find('.userId').val();

        $.ajax({
            url: '/messenger/delete/dialog/' + id,
            success: function (res) {
                if (res.success) {
                    if (isMobile) {
                        ionCart.remove();
                    } else {
                        boxrpt.remove();
                    }
                }
            }
        })
    })

    $('.resetSearch').click(function () {
        delete_cookie('advanceSearch');
        // window.location.reload();
    })

    if ($('.ui.accordion').length) {
        $('.ui.accordion').accordion();
    }

    $('.change-phone form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            data: {
                phone: $(this).find('#phone').val(),
            },
            method: 'post',
            success: function (res) {
                console.log(res);
                if (res.success) {
                    //close
                    resendSms()

                }
            }
        });
    });

    $('.change-email form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            data: {
                email: $(this).find('#email').val(),
            },
            method: 'post',
            success: function (res) {
                console.log(res);
                if (res.success) {
                    //close
                    resendEmail()

                }
            }
        });
    });


    $('.photos .mainPhoto.ui.checkbox.toggle').checkbox({
        onChecked: function () {
            setMainPhoto($(this));
        }
    });
});

$('.change-phone .deny').click(function () {
    resendSms();
})

$('.change-email .deny').click(function () {
    resendEmail();
})

function changeMobileBackground() {
    var mobileBackgroundWillShow = mobileBackgroundNow + 1 <= mobileBackgrounds.length - 1 ? mobileBackgroundNow + 1 : 0;

    $('.login-background ').css('background-image', 'url(' + mobileBackgrounds[mobileBackgroundWillShow] + ')');

    mobileBackgroundNow = mobileBackgroundWillShow;

    setTimeout(function () {
        changeMobileBackground();
    }, 8000)
}


function savePhotoData(data) {

    var url = '/' + $('#save_photo_data_url').val();
    var mainPhotoAlreadyExists = $('#mainPhotoAlreadyExists').val();

    $.ajax({
        url: url,
        type: 'Post',
        data: 'name=' + data.result.public_id + '&mainPhotoAlreadyExists=' + mainPhotoAlreadyExists,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            window.location.href = $('#photos_url').val();
        }
    });
}

function deletePhoto(id, node) {
    var thisIsMainPhoto = node.find('.mainPhoto input').is(":checked");
    node.remove();

    if (thisIsMainPhoto) {
        $('.photo').eq(0).find('.mainPhoto').click();
    }

    $.ajax({
        url: '/user/photo/delete/' + id,
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (otherPhotoId) {
            if (otherPhotoId > 0) {
                var radiobox = $('.photos .mainPhoto').find('input[value="' + otherPhotoId + '"]').parents('.mainPhoto');
                radiobox.click();
            }
            if (!$('.photos .photo').size()) {
                $('#mainPhotoAlreadyExists').val(0);
            }

        }
    });
}

// function setMainPhoto(thisObj){
// 	var id = thisObj.val();
// 	$.ajax({
// 		url: '/user/photo/main/' + id ,
// 		type: 'Post',
// 		error: function(response){
// 			console.log("Error:" + JSON.stringify(response));
// 		},
// 		success: function(response){
//
// 			console.log($('.privatePhoto').attr('disabled', false));;
//
// 			$('.privatePhoto[value="' + id + '"]').attr('disabled', true);
// 			// if (response === 'uncheck') {
// 			// 	$(thisObj).attr('checked', false);
// 			// }
// 			// }else if(response === 'check') {
// 			// 	console.log($('.privatePhoto input[value="' + id + '"]').attr('disabled', false));
// 			// }
// 		}
// 	});
// }

// 		function setPrivatePhoto(thisObj){
// 			var id = thisObj.val();
//
// //	var id = thisObj.attr('data-id');
// 			console.log(id);
// 			$.ajax({
// 				url: '/user/photo/private/' + id ,
// 				type: 'Post',
// 				error: function(response){
// 					console.log("Error:" + JSON.stringify(response));
// 				},
// 				success: function(response){
// 					console.log(response);
// 					if (response === 'uncheck') {
// 						console.log($('.mainPhoto input[value="' + id + '"]').attr('disabled', true));
// 					}else if(response === 'check') {
// 						console.log($('.mainPhoto input[value="' + id + '"]').attr('disabled', false));
// 					}
//
//
// 				}
// 			});
// 		}

function getUserData(id) {

    $.ajax({
        url: '/user/users/' + id,
        type: 'Get',
        error: function (response) {
            //console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            //console.log("Success:" + JSON.stringify(response));
            $('#user_data').html(response);
            $('#profile_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

// function listAction(action, memberId, refreshPage, successMessage) {
// 	$('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");
//
// 	$.ajax({
// 		url: '/user/users/' + action + '/' + memberId,
// 		type: 'Get',
// 		error: function (response) {
// 			console.log("Error:" + JSON.stringify(response));
// 		},
// 		success: function (response) {
// 			//console.log("Success:" + JSON.stringify(response));
// 			if (refreshPage) {
// 				// console.log($('#search_filter_form').find('input[type="submit"]'));
// 				//
// 				// $('#search_filter_form')
// 				// 	//.attr('action', url)
// 				// 	.find('input[type="submit"]')
// 				// 	.click()
// 				// ;
// 				location.reload();
// 			} else {
// 				$('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
// 				alert(successMessage);
// 				if (successMessage == texts.success.shortAddToFavorite) {
// 					$('.add_to_fav').addClass('delete_from_fav').removeClass('add_to_fav').text(texts.shortRemoveFromFavorite)
//
// 					// } else if(successMessage == texts.success.sendRequestToUser) {
// 					// 	//בקשה לראות תמונות
// 					// 	// חסויות נשלחה
// 					// }
// 				}
//
// 			}
// 		}
// 	});
// }

// function favAndBlackListActionsInit() {
// 	$('.add_to_fav, .ask_photo, .add_to_back_list, .delete_from_fav, .delete_from_black_list').unbind('click').bind('click', function (e) {
// 		e.preventDefault();
// 		//var action = ($(this).hasClass('add_to_fav')) ? 'favorite' : 'black_list';
//
// 		var refreshPage = false;
//
// 		if ($(this).hasClass('add_to_fav')) {
// 			var action = 'favorite';
// 			var successMessage = texts.success.addToFavorite;
// 		} else if ($(this).hasClass('add_to_back_list')) {
// 			var action = 'black_list';
// 			var successMessage = texts.success.addToBlackList; // 'משתמש זה הוסף לרשימת החסומים.';
// 		} else if ($(this).hasClass('delete_from_fav')) {
// 			var action = 'favorite/delete';
// 			var successMessage = '';
// 			refreshPage = true;
// 		} else if ($(this).hasClass('delete_from_black_list')) {
// 			var action = 'black_list/delete';
// 			var successMessage = '';
// 			refreshPage = true;
// 		} else if ($(this).hasClass('ask_photo')) {
//
// 			var action = 'ask_photo';
// 			var successMessage = texts.success.sendRequestToUser;
// 			refreshPage = true;
// 		}
//
// 		listAction(action, $(this).parents('.boxcont').find('.userId').val(), refreshPage, successMessage);
// 	});
// }


function contactFormIsValid() {

    var isError = false;

    $('.field_2').removeClass('error');

    $('[required="required"]').each(function () {

        if (!$(this).val().length) {
            $(this).parents('.field_2').addClass('error');
            isError = true;
        }
    });

    if (isError) {
        $('.errors.empty_fields').show();

        $('html, body').animate({
            scrollTop: $('.errors.empty_fields').offset().top - 160
        }, 1000);

        return false;
    }

    return true;

}

/********************* Arena Popup ****************************************************/


// function sendLike(id) {
// 	id = parseInt(id);
// 	alert(id);
// 	if (id > 0) {
// 		jQuery.ajax({
// 			url: '/' + LOCALE +'/user/like/' + id,
// 			type: "POST",
// 			dataType: "json",
// 			success: function (data) {
// 				//alert(data);
// 				$('.like_click[userid="' + id + '"]').each(function () {
// 					//if ($(this).hasClass('nyinner')) {
// 					$(this).addClass('inList');
// 					//} else {
// 					// $(this).remove();
// 					//}
// 				});
// 				//alert($('.popup.pgal0').css('display') != 'block' && $('.like_click[userid="' + id + '"]').first().size() > 0);
// 				if ($('.popup.pgal0').css('display') != 'block'
// 					&& $('.like_click[userid="' + id + '"]').first().size() > 0 )
// 				{
// 					if($('#alert').size() == 0) {
// 						$('body').prepend('<div class="ui modal small" id="alert"><i class="close icon"></i> <div class="header">Notification </div> <div class="content"> <p>Your like was sent successfully.</p> </div> <div class="actions"> <div class="ui button ok">OK</div> </div></div>');
// 					}else{
// 						$('#alert .content').html('<p>Your like was sent successfully.</p>');
// 					}
// 					$('#alert.ui.modal').modal({onApprove : function() { $('body').trigger('refresh'); }}).modal('show');
// 					//alert('Your like was sent successfully.');
// 				}
// 			}
// 		});
// 	}
// }


$('.smsReset').click(function () {
    console.log(123)
    // $('.change-phone').modal('show');
    $('.change-email').modal('show');
    // var elem = $(this);
    // $.ajax({
    // 	url: '/activation/send',
    // 	type: 'post',
    // 	data: {'repeat': true},
    // 	success: function (res) {
    // 		console.log(res);
    // 		if (res.text) {
    // 			alert(res.text);
    // 			elem.parents('.row').remove();
    // 		}
    //
    // 	}
    // });
});


// $('.bingo, .notification').click(function (e) {
// 	var thisObj = $(this);
// 	console.log(thisObj.attr('notification-id'));
// 	$.ajax({
// 		url: '/user/notifications/' + thisObj.attr('notification-id'),
// 		success: function (res) {signUpFormIsValid
// 			thisObj.addClass('readNotif');
// 		}
// 	});
// 	if(thisObj.hasClass('notification')) openPopup($(this).attr('data-id'));
// });

//
// function bingoCheck() {
// 	if(jQuery('#dialog').size() == 0) {
// 		jQuery.ajax({
// 			url: '/user/like/bingo/',
// 			type: "GET",
// 			dataType: "json",
// 			success: function (data) {
// 				if (data && $('#splashBingo').size() == 0) {
// 					var template = jQuery('#splashBingoTemplate').html();
// 					template = template.replace("[attrid]", 'id').replace("[USERNAME]", data.username).replace("[Photo1]", data.photo1).replace("[Photo2]", data.photo2).replace("%5BCONTACTID%5D", data.contact_id);
// 					jQuery('body').prepend(template);
// 					bingoShow(data.id);
// 				} else {
// 					setTimeout(function () {
// 						bingoCheck();
// 					}, 10000);
// 				}
// 			}
// 		});
// 	}
// }


//
// window.scroll = function () {
//
// 	var sc = Math.ceil(document.documentElement.scrollTop - 17);
// 	var wh = window.innerHeight;
// 	var dh = document.documentElement.scrollHeight;
//
// 	var edh = wh * 3;
// 	if (sc + wh >= dh - 300) {
//
// 	}
// 	;
//
// };


$('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");


// $('.cloudinaryForm input[type="file"]').change(function(){
// 	$('.browseinput').val($(this).val());
// });


//	//alert(1);
$('.photoInput').change(function () {
    //	 alert(2);
    //  $(this).closest('form').find('input[type="submit"]').first().click();
    //$('.submitPhoto').click();
    //$('.registrPhotoInput').change();
});


$('.removePhoto').click(function (e) {
    e.preventDefault();
    var id = $(this).attr("id");
    var node = $(this).parents('.photo');

    $('.ui.basic.modal')
        .modal({
            closable: true,
            onApprove: function () {
                deletePhoto(id, node);
            }
        })
        .modal('show')
    ;
});


$('.photos .mainPhoto.ui.checkbox.toggle').on('change', {
    function() {
        if (this.checked) {
            setMainPhoto($(this));
        }
    }
});

$('.privatePhoto').click(function () {
    console.log($(this));
    setPrivatePhoto($(this));
});


$('.sidebarPhoto').each(function () {

    if ($(this).hasClass('sidebarPhoto')) {
        var width = 128;
        var height = 154;
    } else {
        var width = 184;
        var height = 218;
    }

    var photoName = $(this).siblings('input[type="hidden"]').val();

    var genderId = $(this).parents('.boxcont').find('.userGenderId').val();
    console.log(genderId);

    var url = photoName;


    // $(this).attr('src',url)
    //.css({"width":width, "height": height})
    ;
    // if($(this).attr('data-src').length > 0){
    // 	$(this).attr('src', $(this).attr('data-src'));
    // }
});

$('.submitPhoto').click(function (e) {
    e.preventDefault();
});


/******************** Articles Cloudinary *********************************************/

// if ($('.magcont').size() || $('.hp-articles').size()) {
// 	if ($('.previewImageName').size()) {
// 		$('.previewImageName').each(function () {
// 			var url = $.cloudinary.url($(this).val(), {width: 184, height: 218, crop: 'fill'});
// 			// $(this).siblings('img').attr('src',url);
// 		});
// 	}
//
// 	if ($('.imageName').size()) {
// 		var url = $.cloudinary.url($('.imageName').val(), {crop: 'fill'});
// 		// $('.imageName').siblings('img').attr('src',url);
// 	}
//
//
// 	if ($('.homepageImageName').size()) {
// 		$('.homepageImageName').each(function () {
// 			var url = $.cloudinary.url($(this).val(), {width: 176, height: 176, crop: 'fill'});
// 			// $(this).siblings('img').attr('src',url);
// 		});
// 	}
// }

/******************** Home Page Cloudinary *********************************************/

// if ($('.slides').size()) {
//
// 	$('.imageName').each(function () {
// 		var url = $.cloudinary.url($(this).val(), {format: 'jpg'});
// 		$(this).parents('.slide').css('background', 'url(' + url + ') no-repeat top center');
// 	});
//
// }


// $('.faceImageName').each(function () {
// 	var url = $.cloudinary.url($(this).val(), {
// 		width: 200,
// 		height: 200,
// 		crop: 'thumb',
// 		gravity: 'face',
// 		radius: 'max',
// 		format: 'png'
// 	})
// 	//console.log(url);
// 	$(this).siblings('.url').val(url);
// });


/******************** Messages Page Cloudinary *********************************************/

if ($('.hotlist').size()) {

    $('.hotlist .userimg input[type="hidden"]').each(function () {
        var url = $.cloudinary.url($(this).val(), {
            width: 86,
            height: 86,
            crop: 'thumb',
            gravity: 'face',
            format: 'png'
        })
        //console.log(url);
        // $(this).parent().find('img').attr('src',url);
    });

}


/**************************************************************************/

$('.errors ul').addClass("list");


/*
$('#advancedSearch_withPhoto').click(function(){
    var filter = $(this).is(':checked') ? 'photo' : $('#search_filter_by_default').val();
    $('#advancedSearch_filter').val(filter);
});
*/

// $('#searchFilter').change(function () {
// 	$('#advancedSearch_filter').val($(this).val());
// 	$('#search_filter_form')
// 	// change page number to 1 in url when switching filter
// 		.attr('action', $('#search_url_when_switching_filter').val())
// 		.find('input[type="submit"]')
// 		.click()
// 	;
// });
//
// $('.usersResults .first a, .usersResults .previous a, .usersResults .page a, .usersResults .next a, .usersResults .last a').click(function (e) {
// 	e.preventDefault();
// 	var url = $(this).attr('href');
//
// 	$('#search_filter_form')
// 		.attr('action', url)
// 		.find('input[type="submit"]')
// 		.click()
// 	;
// });
//
// $('.rptimgbox.boxcont').popup();
//
// $('.tgl_qs').click(function () {
// 	$(this).toggleClass('open');
// 	$('#quick_search_sidebar_form').toggle();
// });
//
// $('.tgl_mqs').click(function () {
// 	$(this).toggleClass('open');
// 	$('.qs').toggle();
// });
//
// $('.nextstage').click(function (e) {
// 	e.preventDefault();
// 	if (signUpFormIsValid()) {
// 		$('#next_stage').click();
// 	}
// });
//
// $('.contact_submit').click(function (e) {
// 	e.preventDefault();
// 	if (contactFormIsValid()) {
// 		$('#send').click();
// 	}
// });


/*

    $('.bottom_menu .item').bind('touchstart', function(){
        $(this).addClass('active');
    });

    $('.bottom_menu .item').bind('touchend', function(){
        $(this).removeClass('active');
    })

*/


function savePhotoData(data) {

    var url = '/' + $('#save_photo_data_url').val();
    var mainPhotoAlreadyExists = $('#mainPhotoAlreadyExists').val();

    $.ajax({
        url: url,
        type: 'Post',
        data: 'name=' + data.result.public_id + '&mainPhotoAlreadyExists=' + mainPhotoAlreadyExists,
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            window.location.href = $('#photos_url').val();
        }
    });
}

function deletePhoto(id, node) {
    var thisIsMainPhoto = node.find('.mainPhoto input').is(":checked");
    node.remove();


    $.ajax({
        url: '/user/photo/delete/' + id,
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (otherPhotoId) {
            console.log(otherPhotoId);
            if (otherPhotoId > 0) {
                var radiobox = $('.photos .mainPhoto').find('input[value="' + otherPhotoId + '"]').parents('.mainPhoto');
                console.log('sdfs!E#Ewfs$');
                radiobox.click();
            }
            if (!$('.photos .photo').size()) {
                $('#mainPhotoAlreadyExists').val(0);
            }

            if (thisIsMainPhoto) {
                // console.log( $('.photo').eq(0).find('.mainPhoto'));
                // $('.photo').eq(0).find('.mainPhoto').click();
            }

        }
    });

}

function setMainPhoto(thisObj) {
    var id = thisObj.val();
    $.ajax({
        url: '/user/photo/main/' + id,
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {

            // if (isPay) {
            $('.privatePhoto').attr('disabled', false);
            // }
            $('.privatePhoto[value="' + id + '"]').attr('disabled', true);
            // if (response === 'uncheck') {
            // 	$(thisObj).attr('checked', false);
            // }
            // }else if(response === 'check') {
            // 	console.log($('.privatePhoto input[value="' + id + '"]').attr('disabled', false));
            // }
        }
    });
}

function setPrivatePhoto(thisObj) {
    var id = thisObj.val();

//	var id = thisObj.attr('data-id');
    console.log(id);
    $.ajax({
        url: '/user/photo/private/' + id,
        type: 'Post',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            console.log(response);
            if (response.state === 'uncheck') {
                console.log($('.mainPhoto input[value="' + id + '"]').attr('disabled', true));
            } else if (response.state === 'check') {
                if (response.validPhoto || !response.user.hasValidMain) {
                    console.log($('.mainPhoto input[value="' + id + '"]').attr('disabled', false));
                }
            }


        }
    });
}

function getUserData(id) {

    $.ajax({
        url: '/user/users/' + id,
        type: 'Get',
        error: function (response) {
            //console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            //console.log("Success:" + JSON.stringify(response));
            $('#user_data').html(response);
            $('#profile_dimmer').addClass("disabled").find('.loader').addClass("disabled");
        }
    });
}

function listAction(action, memberId, refreshPage, successMessage, isFullProf, elem) {
    //alert(1)
    //$('#global_dimmer').removeClass("disabled").find('.loader').removeClass("disabled");

    $.ajax({
        url: '/user/users/' + action + '/' + memberId,
        type: 'Get',
        error: function (response) {
            console.log("Error:" + JSON.stringify(response));
        },
        success: function (response) {
            //console.log("Success:" + JSON.stringify(response));
            if (refreshPage) {
                location.reload();
            } else {
                console.log(action);
                $('#global_dimmer').addClass("disabled").find('.loader').addClass("disabled");
                alert(successMessage);

                // alert(2)
                // if is desktop
                if (!$('.isMobile').val()) {

                    // alert(action)
                    if (action == 'favorite') {
                        // alert(isFullProf)
                        if (isFullProf) {

                            // alert(1)
                            $('.btn-favorite').toggleClass('hidden-important');
                        }
                        $('ul .add_to_fav').removeClass('add_to_fav').addClass('delete_from_fav')
                            .text(texts.removeFromFavorite);


                    } else if (action == 'favorite/delete') {
                        if (isFullProf) {
                            // alert(2)
                            $('.btn-favorite').toggleClass('hidden-important');
                        }
                        $('ul .delete_from_fav').removeClass('delete_from_fav').addClass('add_to_fav')
                            .text(texts.addToFavorite);

                    } else if (action == 'black_list') {
                        elem.addClass('delete_from_black_list').removeClass('add_to_back_list').text(texts.removeFromBlackList)

                    } else if (action == 'black_list/delete') {
                        elem.addClass('add_to_back_list').removeClass('delete_from_black_list').text(texts.addToBlackList)

                    }
                } else {
                    if (action === 'favorite') {

                        elem.addClass('delete_from_fav').removeClass('add_to_fav')
                            .find('li').addClass('remove-green').removeClass('add-green');
                        if (!isFullProf) {
                            elem.find('div').text('remove');
                        }

                        // if (isFullProf) {
                        // 	console.log($('.btn-favorite'))
                        // 	$('.btn-favorite')//.toggleClass('hidden-important');
                        // 	/** if add to favs from full user profile */
                        //
                        //
                        // } else {
                        // 	/** if add to favs from users list, mini-profiles */
                        // 	console.log(elem);
                        // 	elem.addClass('delete_from_fav').removeClass('add_to_fav')
                        // 		.find('li').addClass('remove-green').removeClass('add-green').next().text('הסר');
                        //
                        // }
                    } else if (action == 'favorite/delete') {

                        elem.addClass('add_to_fav').removeClass('delete_from_fav')
                            .find('li').removeClass('remove-green').addClass('add-green');
                        if (!isFullProf) {
                            elem.find('div').text('add');
                        }

                        // if (isFullProf) {
                        // 	/** if remove from favs from full user profile */
                        // 	elem.addClass('add_to_fav').removeClass('delete_from_fav')
                        // 		.find('li').addClass('remove-green').removeClass('remove-green');
                        //
                        // } else {
                        // 	/** if remove from favs from users list, mini-profiles */
                        // 	console.log(elem);
                        // 	elem.addClass('add_to_fav').removeClass('delete_from_fav')
                        // 		.find('li').removeClass('remove-green').addClass('add-green').next().text('הוסף');
                        //
                        // }
                    } else if (action == 'black_list') {
                        elem.addClass('delete_from_black_list').removeClass('add_to_back_list');
                        elem.find('i').addClass('unlock').removeClass('lock');
                        elem.find('span').text('unblock')
                    } else if (action == 'black_list/delete') {
                        elem.addClass('add_to_back_list').removeClass('delete_from_black_list');
                        console.log(elem.find('i'));
                        elem.find('i').addClass('lock').removeClass('unlock');
                        elem.find('span').text('block');

                        if (elem.attr('data-list')) {
                            elem.parents('.user-section').remove();

                            if (!$('.user-section').length) {
                                $('.noResults').removeClass('hidden');
                            }
                        }


                    }
                }
            }
        }
    });
}


function signUpFormIsValid() {
    var isError = false;

    $('.field').removeClass('error');

    $('[required="required"]').each(function () {
        if (!$(this).val().length) {
            $(this).parents('.field').addClass('error');
            isError = true;
        }

        if ($(this).attr('id') == 'sign_up_three_agree') {
            if (!$(this).is(':checked')) {
                $(this).parents('.field').addClass('error');
                isError = true;
            }
        }

    });

    console.log($('#signUpThree_agree').size(), $('#signUpThree_agree').is(":checked"))
    ////alert(1)
    if ($('#signUpThree_agree').size() && !$('#signUpThree_agree').is(":checked")) {
        // alert(2)
        $('#signUpThree_agree').parents('.field').addClass('error');
        isError = true;
    }

    if (isError) {
        $('.errors.empty_fields').show();

        $('html, body').animate({
            scrollTop: $('.errors.empty_fields').offset().top - 130
        }, 1000);

        return false;
    }

    return true;

}

function contactFormIsValid() {

    var isError = false;

    $('.field_2').removeClass('error');

    $('[required="required"]').each(function () {

        if (!$(this).val().length) {
            $(this).parents('.field_2').addClass('error');
            isError = true;
        }
    });

    if (isError) {
        $('.errors.empty_fields').show();

        $('html, body').animate({
            scrollTop: $('.errors.empty_fields').offset().top - 160
        }, 1000);

        return false;
    }

    return true;

}

/********************* Arena Popup ****************************************************/

var popslide = '';


// jQuery(document).ready(function ($) {
// 	//alert(1);
// 	$('.pop_con a').click(function () {
// 		arenaClick(this);
// 		return false;
// 	});
// 	popslide = jQuery('.popup.pgal0 .popslider').lightSlider();
//
//

//

//
// });

function arenaClick(el) {
    var alt = jQuery(el).find('img').attr('alt');
    if (alt !== 'redthumb') {
        var id = ARENA_USERS[SHOW_NOW]['id'];
        console.log(SHOW_NOW);
    }

    if (alt == 'pmsg') {
        window.location.href = $('#dialogLink').val().replace('CONTACT_ID', id);
    }

    if (alt == 'redthumb') {
        // alert('redthumb');
        if (ARENA_USERS.length - 1 >= 0) {
            $('.username').parents('a').attr('href', $('.username').parents('a').attr('href')
                .replace(ARENA_USERS[SHOW_NOW]['id'], 'userID'));

            ARENA_USERS.splice(SHOW_NOW, 1);
        }
        $('.arrow.circle.right.icon').click();
        // alert('You liked ' + ARENA_USERS[SHOW_NOW]['username'])
    }
    if (alt == 'thumbgreen') {
        // alert('thumbgreen')
        // 	alert(texts.youLiked + ARENA_USERS[SHOW_NOW]['username']);
        sendLike(id);
        if (ARENA_USERS.length - 1 >= 0) {
            ARENA_USERS.splice(SHOW_NOW, 1);
        }
        $('.arrow.circle.right.icon').click();

    }
    //$('.pop_title').text('test');
    console.log(ARENA_USERS);
}

function sendLike(id) {
    id = parseInt(id);
    // alert(id);
    if (id > 0) {
        jQuery.ajax({
            url: '/user/like/' + id,
            type: "POST",
            dataType: "json",
            success: function (data) {
                //alert(data);
                $('.like_click[userid="' + id + '"]').each(function () {
                    //if ($(this).hasClass('nyinner')) {
                    $(this).addClass('inList');
                    //} else {
                    // $(this).remove();
                    //}
                });
                //alert($('.popup.pgal0').css('display') != 'block' && $('.like_click[userid="' + id + '"]').first().size() > 0);
                if ($('.popup.pgal0').css('display') != 'block'
                    && $('.like_click[userid="' + id + '"]').first().size() > 0) {
                    if ($('#alert').size() == 0) {
                        $('body').prepend('<div class="ui modal small" id="alert"><i class="close icon"></i> <div class="header">Notification </div> <div class="content"> <p>Your like was sent successfully.</p> </div> <div class="actions"> <div class="ui button ok">OK</div> </div></div>');
                    } else {
                        $('#alert .content').html('<p>Your like was sent successfully.</p>');
                    }
                    $('#alert.ui.modal').modal({
                        onApprove: function () {
                            $('body').trigger('refresh');
                        }
                    }).modal('show');
                    //alert('Your like was sent successfully.');
                }
            }
        });
    }
}

// $('.bingo, .notification').click(function (e) {
// 	var thisObj = $(this);
// 	console.log(thisObj.attr('notification-id'));
// 	$.ajax({
// 		url: '/user/notifications/' + thisObj.attr('notification-id'),
// 		success: function (res) {
// 			thisObj.addClass('readNotif');
// 		}
// 	});
// 	if(thisObj.hasClass('notification')) openPopup($(this).attr('data-id'));
// });


$('.registrPhotoInput').change(function (e) {
    // //alert(1);
    e.preventDefault();
    console.log($('.registrPhotoInput').prop('files')[0]);
    var formData = new FormData();

    formData.append("photo", $('.registrPhotoInput').prop('files')[0]);
    $.ajax({
        url: '/user/photo/data',
        type: 'post',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = ((evt.loaded / evt.total) * 100);

                    $(".bar").css({'width': parseInt(percentComplete) + '%'});
                    $(".bar .progress").html(parseInt(percentComplete) + '%');
                    if (percentComplete > 99) {
                        $('.ui.progress').addClass('success');
                        // location.reload()
                        $('#global_dimmer').removeClass('disabled').find('div').removeClass('disabled')

                    }
                }
            }, false);
            return xhr;
        },
        beforeSend: function () {
            $('.ui.progress').css({'display': 'block'});
            //$('#uploadStatus').html('<img src="images/loading.gif"/>');
        },

        success: function (res) {
            if (res == 'maxCount') {
                alert('you may upload up to 8 photos. ')
            } else {
                localStorage.setItem('uploadImage', res.id);
                // 	$.ajax({
                // 		url: '/user/photo/data/add/' + res.id,
                // 		success: function () {
                // 			// alert(1)
                // 			location.href = '/user/profile/4'
                // 		}
                // 	})
                // }
                // console.log(res);
                location.href = '/user/profile/4'

            }
        },

        error: function (err) {
            // localStorage.setItem('uploadImage', res.id);
            location.href = '/user/profile/4'
        }

    });
});


$('.activation-problems').click(function () {
    if ($('.harsama-midsec').css('display') == 'none') {
        $('.harsama-midsec').fadeIn(300)
    } else {
        $('.harsama-midsec').fadeOut(300)
    }
})


var can = $('.users').size() ? true : false;
// var cachePage = 0;
// var currentPage = Number($('.searchStep').val());


$(window).scroll(function () {

    if (can) {
        var h = $(window).scrollTop() + $(window).height();
        var isMobile = $('.isMobile').val();
        var dh = $(document).height();
        var page;


        var scrollTo = '';

        if (/*(!isMobile && (h >= currentPage * 3000) || isMobile &&*/  h >= dh / 1.5) {

            page = isMobile ? Number($('.user-section').last().attr('data-page')) + 1
                : Number($('.users .boxcont').last().attr('data-page')) + 1;
            if (page < 0) {
                page = 2;
            }
            // alert(page);

            if (page == 2 && $('.noMoreResults').html()) {
                $('.noMoreResults').remove();
            }
            scrollTo = 'bottom';

            var cantTopLazyLoad;
            if (isMobile) {
                cantTopLazyLoad = $('.user_section').first().attr('data-page') == 1
            } else {
                cantTopLazyLoad = $('.users .boxcont').first().attr('data-page') == 1
            }
        } else if (window.scrollY < 90 && !cantTopLazyLoad) {

            scrollTo = 'top';
            page = isMobile ? Number($('.user-section').first().attr('data-page')) - 1
                : Number($('.users .boxcont').first().attr('data-page')) - 1;
            // console.clear();
            // console.log($('.users .boxcont').first());

            if (page === 0) {
                scrollTo = false;
            }
            console.log(page);

            console.log('LOAD NEW USERS')
        }


        if (scrollTo) {

            can = false;

            var data = $('.searchData').val() ? JSON.parse($('.searchData').val()) : {};
            // var filter = $('#search_filter_form #advancedSearch_filter').val();
            if (parseInt(page) < 0) {
                page = 2;
                $('.noMoreResults').remove();
            }

            if ($('.noMoreResults').css('display') == 'none' || $('.noMoreResults').length == 0) {

                $.ajax({
                    url: $('.requestUrl').val() + '/' + page + '/1',
                    dataType: 'text',
                    type: 'post',
                    data: {
                        'ajax_data': data
                    },
                    success: function (res) {
                        window.history.replaceState(null, null, $('.requestUrl').val() + '/' + page + '/0');
                        // delete_cookie('page');
                        // delete_cookie('userId');


                        res = JSON.parse(res);
                        console.log(res);
                        if (res.users && page > 0) {


                            if ($('.isMobile').val()) {
                                mobileSearchRender(res.users.users, res.page, scrollTo == 'bottom');
                            } else {
                                searchRender(res.users, res.page, scrollTo == 'bottom');
                            }
                            if (res.users.users.length < 6 && !$('.noMoreResults').html()) {
                                $('.users').append('<div class="noMoreResults"> <p> no more results </p></div>');
                            }
                        } else if (page > 0) {
                            if ($('.isMobile').val()) {
                                mobileSearchRender(res.newUsers.users, res.page, scrollTo == 'bottom');
                                console.log(res.newUsers.users);
                            } else {
                                searchRender(res.newUsers, res.page, scrollTo == 'bottom');
                            }
                            if (res.newUsers.users.length < 10 && !$('.noMoreResults').html()) {
                                $('.users').append('<div class="noMoreResults"> <p> no more results </p></div>');
                            }
                        } else {
                            // topLazyLoad = false;
                            if (page == 0) {
                                can = true;
                            }
                        }


                    },
                    error: function (request, status, error) {
                        can = true;
                    }
                });
            }
        }
    }

});

var cand = true
var isMobile = $('.isMobile').val();
var dialogscroll = isMobile ? window : '#dialogs';
$(dialogscroll).scroll(function () {
    if ($('#dialogs').length > 0 && cand) {
        var h = $(dialogscroll).scrollTop();
        var dh = $(dialogscroll).height();
        var page;
        var cantTopLazyLoad = $('#dialogs .user-inbox').first().attr('data-page') == 1

        console.log(h);
        console.log(dh);

        var scrollTo = '';

        if (/*(!isMobile && (h >= currentPage * 3000) || isMobile &&*/  h >= dh * 2) {

            page = Number($('#dialogs .user-inbox').last().attr('data-page')) + 1;


            if (page == 2 && $('.noMoreResults').html()) {
                $('.noMoreResults').remove();
            }
            scrollTo = 'bottom';

            cantTopLazyLoad = $('#dialogs .user-inbox').first().attr('data-page') == 1

        } else if ((window.scrollY < 90 && !cantTopLazyLoad) || h < 20) {

            scrollTo = 'top';
            page = Number($('#dialogs .user-inbox').first().attr('data-page')) - 1;
            // console.clear();
            // console.log($('.users .boxcont').first());

            if (page === 0) {
                scrollTo = false;
            }
            console.log(page);

            console.log('LOAD NEW USERS')
        }


        if (scrollTo) {

            cand = false;

            // if(parseInt(page) < 2){
            // 	page = 2;
            // 	$('.noMoreResults').remove();
            // }
            if (!$('.noMoreResults').html()) {

                $.ajax({
                    url: '/user/messenger/' + page + '/1',
                    dataType: 'html',
                    type: 'post',
                    data: '',
                    success: function (res) {
                        window.history.replaceState(null, null, $('.requestUrl').val() + '/' + page + '/0');
                        // delete_cookie('page');
                        // delete_cookie('userId');

                        console.log(res);
                        if (scrollTo == 'bottom') {
                            $('#dialogs').append(res);
                        } else {
                            $('#dialogs').prepend(res);
                        }

                        if (res.length == 0) {
                            $('#dialogs').append('<div class="noMoreResults"> <p> no more results </p></div>');
                            cand = false;
                        } else {
                            cand = true;
                        }


                    },
                    error: function (request, status, error) {
                        cand = true;
                    }
                });
            }
        }
    }

});


function searchRender(users, page, addToBottom) {
    console.log('in searchRender')
    var search = $('.users .boxcont').first().hasClass('search');
    users.users.forEach(function (user) {
        var genderName = '';
        switch (user.gender) {
            case 1:
                genderName += texts.man;
                break;
            case 2:
                genderName += texts.woman;
                break;
            case 3:
                genderName += 'trans women';
                break;
            case 4:
                genderName += ' trans man';
                break;
            default:
                break;
        }
        if (search) {
            var text = '<div class="boxcont search" data-page="' + page + '" id="' + user.id + '">'
        } else {
            var text = '<div class="boxcont" data-page="' + page + '" id="' + user.id + '">'
        }
        text += '<input type="hidden" class="userId" value="' + user.id + '">' +

            ' <input type="hidden" class="userGenderId" value="' + user.gender + '">' +
            ' <div class="greyboxcont clearfix">' +
            '<div class="userimg" data-href="/user/users/' + user.id + '">' +
            '<a class="f1" href="/user/users/' + user.id + '" data-href="/user/users/' + user.id + '">';
        if (user.isOnline) {
            text += '<div class="online"></div>';
        }
        text += '<div class="user-image" data-href="/user/users/' + user.id + '" style="background-image: url(' + user.photo + ')"' +
            '</a></div>';


        if (user.isPaying) {
            text += '<div class="queen-icon"></div>';
        }

        if (user.isVerify) {
            text += '<div class="verify-icon"></div>';
        }


        text += '</div>';

        text += '<div class="user-section-btns">';
        text += '<button class="btn-send-msg users-list">';
        text += '<a href="/user/messenger/dialog/open/userId:' + $('.currentUserId').val() + '/contactId:' + user.id + '">';
        text += 'Send me a message';
        text += '</a></button>';

        if (user.isNew) {
            text += '<div class="imgicon"><img src="/images/main/new.png" title="new user" alt=""></div> ';
        }

        text += '</div>';
        text += '<div class="clear"></div>';

        text += '<div class="lftcontbox clearfix">' +
            '<div class="onlinediv clearfix">';

        // if (user.isOnline) {
        // 	text += '<a href="' + '/user/messenger/dialog/open/userId:' + $('.currentUserId').val() + '/contactId:' + user.id + '"" >' +
        // 		'<div class="imgbox"><img src="/images/main-pg-online-icon.png" alt=""></div>' +
        // 		// '<div class="statustitle">On<span>line</span></div>' +
        // 		'</a>';
        // }

        text += '</div>' +
            '<div class="subtitle clearfix">' +
            '<div class="subtitletext" data-href="/user/users/' + user.id + '">' +
            ' <a class="f1" href="/user/users/' + user.id + '" data-href="/user/users/' + user.id + '"> ' + user.username + '  </a>' +
            '</div>' +
            '<div class="imgicon">';


        text += '</div></div>' +

            '<div class="midcontdes">' +
            genderName + ', ' + user.age + '<br> '
            + user.area_name + '<br>' +
            user.relationshipStatus + ', for: ' + user.lookingFor + '<br><br>';

        if (user.about.length > 50) {
            text += user.about.slice(0, 50) + '...';
        } else {
            text += user.about;
        }
        text += '</div>';
        // user.about.length > 50 ? user.about.slice(0,50) +'...' : user.about +

        var test = "                 <div class=\"midcontdes\">\n" +
            "                                {{ user.gender.name }}, {{ user.age }} <br> {{ user.city.name }}\n" +
            "                                <br>{{ user.relationshipStatus.name }},\n" +
            "                                {% trans %} For {% endtrans %} {{ user.lookingFor.name }}\n" +
            "                                <br>\n" +
            "                                <br>\n" +
            "                                {{ user.about|length > 50 ? user.about|slice(0, 50) ~ '...' : user.about  }}\n" +
            "                            </div>";


        text += '</div>';

        text += '<div class="lightgrey clearfix">' +
            ' <ul class="smlinks clearfix">';
        if ($('.currentUserId').val() > 0) {

            text += '<li><a href="/user/users/' + user.id + '" data-href="/user/users/' + user.id + '">' +
                texts.viewProfile
                + '</a></li>';

            text += '<li><a href="/user/messenger/dialog/open/userId:' + $('.currentUserId').val() + '/contactId:' + user.id + '">' +
                texts.sendMessage
                + '</a></li>';

            if (!user.isAddFavorite) {
                text += '<li><a class="add_to_fav" href="#">' + texts.addToFavorite + '</a></li>'
            } else if (user.isAddFavorite) {
                text += '<li><a class="delete_from_fav" href="#">' + texts.removeFromFavorite + '</a></li>'
            }

            if (!user.isAddBlackListed) {
                text += ' <li><a class="add_to_back_list" href="#">' + texts.addToBlackList + '</a></li>';
            } else if (user.isAddBlackListed) {
                text += ' <li><a class="delete_from_black_list" href="#">' + texts.removeFromBlackList + '</a></li>'
            }

            if (user.photo && !(user.phoro == "/images/no_photo_1.jpg" || user.phoro == "/images/no_photo_2.jpg")) {
                if (!user.isAddLike) {
                    text += '<li><a class="addLike" data-username="' + user.username + '"';
                    text += 'data-id="' + user.id + '" href="">' + texts.like + '</a></li>'
                }

            }
        }

        text += '</ul></div>'
        // console.log($('.users'));
        if (addToBottom) {
            $('.users').append(text);
        } else {
            $('.users').prepend(text);
        }

    });
    favAndBlackListActionsInit();
    if (!addToBottom) {
        window.scrollBy(0, 2800)
    }
    can = true;
    //
    //                 {% if app.user.mainPhoto and user.mainPhoto %}
    //                     <li><a class="addLike {% if user.isAddLike(app.user) %} a-disabled {% endif %}" data-id="{{ user.id }}" href="">לייק בזירה</a></li>
    //                 {% endif %}
    //
    //             {% endif %}
    //         </ul>
    //     </div>
    // </div>

    // }

}


function mobileSearchRender(users, page, addToBottom) {
    // alert(5);
    var search = $('.user-section').first().hasClass('search');

    users.forEach(function (user) {
        var text = '<div class="user-section';
        if (user.canWriteTo == false) {
            console.log(text);
            //text.replace("user-section", "user-section disabled");
            text += ' disabled';
            console.log(text);
        }
        if (search) {
            text += ' search';
            //text = '<div class="user-section search" id="' + user.id + '" data-page="' + page + '">';
            //} else {
            //text = '<div class="user-section" id="' + user.id + '" data-page="' + page + '">';
        }
        text += '" id="' + user.id + '" data-page="' + page + '">';

        if (user.isPaying) {
            text += '<div class="paying"></div> ';
        }
        if (user.isOnline) {
            text += '<div class="online"></div>';
        }
        if (user.isNew) {
            text += ' <div  class="new-user"></div>';
        }
        if (user.isVerify) {
            text += '<div class="verify-icon"></div>';
        }

        text += '<div class="avatar" style="background-image: url(' + user.photo + ');" data-href="/user/users/' + user.id + '">';
        text += '</div>'

        text += '<div class="username-section">';
        text += '<div class="row">';
        // text += '<span class="name {{ user.username|length >= 12 ? \'long-name\' : \'\' }}">{{user.username}}, </span>';
        text += '<span class="name ' + user.username.length >= 12 ? 'long-name' : '' + user.username + ', </span>';
        text += '<span class="age">' + user.age + '</span>';
        text += '</div>';

        text += '<div class="row">';
        text += '<span class="location">' + user.area_name + '</span>';
        text += '<span class="distance">' + user.distance + '</span>';
        text += '<div class="clear-both"></div></div></div>';

        text += '<ul class="profile-btn">';
        if ($('.currentUserId').val()) {
            // console.error($('.userId').val());
            // text += ' <a href="/user/messenger/dialog/open/userId:' + $('.currentUserId').val() + '/contactId:' + user.id + '" style="color: #47494d">';
            text += '<div  class="btn profile-div-li" data-id="' + user.id + '">';
            text += '<li class="mobmsg" data-href="/user/messenger/dialog/open/userId:' + $('.currentUserId').val() + '/contactId:' + user.id + '"></li>';
            text += '<div class="mobile-profile-btns">' + texts.sendMessage + '</div>';
            // text += '</a>';
            text += '</div>';

            // text += '<div class="btn profile-div-li addLike {{ app.user.isAddLike(user) or not (app.user.mainPhoto and user.mainPhoto) ? \'a-disabled\' : \'\' }}" data-id="{{ user.id }}">';
            // text += '<div class="btn profile-div-li addLike '+ user.isAddLike || !(user.) +'" data-id="{{ user.id }}">';
            // text += '<div class="btn profile-div-li addLike " data-id="{{ user.id }}">';

            // if (user.photo && !(user.photo == "/images/no_photo_1.jpg" || user.photo == "/images/no_photo_2.jpg")) {
            if (user.isAddLike) {
                text += '<div class="btn profile-div-li addLike a-disabled" data-id="' + user.id + '">';
            } else {
                text += '<div class="btn profile-div-li addLike " data-id="' + user.id + '" data-username="' + user.username + '">';
            }
            ///text += 'data-id="' + user.id + '" href="">' + texts.like + '</a></li>'
            // }
            text += '<li class="like-red"></li>';
            text += '<div class="mobile-profile-btns">' + texts.like + '</div>';
            text += '</div>';
            if (!user.isAddFavorite) {
                text += '<div class="btn profile-div-li add_to_fav">';
                text += '<li class="add-green"></li>';
                text += '<div class="mobile-profile-btns">' + texts.shortAddToFavorite + '</div>';
                text += '</div>';
            } else {
                text += '<div class="btn profile-div-li delete_from_fav">';
                text += '<li class="remove-green"></li>';
                text += '<div class="mobile-profile-btns">' + texts.shortRemoveFromFavorite + '</div>';
                text += '</div>';
            }
        }
        // text += '</div>';
        text += '</ul>';

        text += '<div class="clear"></div></div>';

        if (addToBottom) {
            $('.users').append(text);
        } else {
            $('.users').prepend(text);
        }


    });
    if (addToBottom) {
        $('.users').append('<div class="clear"></div>')
    } else {
        window.scrollBy(0, 1465)
        $('.users').prepend('<div class="clear"></div>')
    }
    favAndBlackListActionsInit();
    can = true;

}

$(document).on("mobileinit", function () {
    // //alert(1)
});


/**
 * send request for add or remove user from favorites
 *
 * @param add: boolean   by default true. Remove if false
 * @param user integer   ID of the user that will added or removed
 *
 * @return undefined
 *
 * */

function addFavs(userId, add = true) {
    action = add ? 'favorite' : 'favorite/remove';
    $.ajax({
        url: '/user/users/' + action + '/' + userId,
    });
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function delete_cookie(name) {
    console.log('in delette cookie');
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
}

function returnToResults(userId, page) {

    console.log('in return to results')
    setTimeout(function () {
        console.log('scroll')
        // alert('test')
        var hash = $('#' + readCookie(userId));
        // console.log(hash.offset().top)
        if (hash.offset()) {
            var top = hash.offset().top;


            window.scrollTo(0, top);

            delete_cookie(userId)


        }
    }, 1000)
}

// var calling = setInterval(function () {
// 	var audio = new Audio('https://m.richdate.co.il/phone_ringing.mp3');
// 	audio.play();
// }, 9000)


function toVideoChat(userId) {
    $.ajax({
        url: '/app_dev.php/video/call/' + userId,
        success: function () {
            $.ajax({
                url: '/app_dev.php/video/call/push/' + userId,
            })
        }
    })
}

var getDataFromServerTimeout;

function getDataFromServer() {

    clearTimeout(getDataFromServerTimeout);
    console.log($('#apiKey').val())
    if ($('#apiKey').length > 0) {
        $.ajax({
            headers: {'apiKey': $('#apiKey').val()},
            dataType: 'json',
            context: this,
            success: function (res) {
                if (res.statistics) {
                    var stat = res.statistics;
                    if (stat.newMessagesNumber > 0) {
                        $('.counter.message-counter, .menu-num.inbox_count, .notif-counter.messages').removeClass('hidden').text(stat.newMessagesNumber);
                    }

                    if (stat.newNotificationsNumber > 0) {
                        $('.notif-counter.arena, .menu-num.arena, .counter.arena').removeClass('hidden').text(stat.newNotificationsNumber);
                    }

                    if (stat.showPhoto > 0) {
                        $('.notif-counter.showPhoto, .menu-num.showPhoto').removeClass('hidden').text(stat.showPhoto);
                    }

                    if (stat.viewedMe > 0) {
                        $('.notif-counter.viewedMe, .menu-num.viewedMe').removeClass('hidden').text(stat.viewedMe);
                    }

                    if (stat.viewed > 0) {
                        $('.notif-counter.viewed, .menu-num.viewed').removeClass('hidden').text(stat.viewed);
                    }

                    if (stat.connected > 0) {
                        $('.notif-counter.connected, .menu-num.connected').removeClass('hidden').text(stat.connected);
                    }

                    if (stat.connectedMe > 0) {
                        $('.notif-counter.connectedMe, .menu-num.connectedMe').removeClass('hidden').text(stat.connectedMe);
                    }

                    if (stat.favorited > 0) {
                        $('.notif-counter.favorited, .menu-num.favorited, .counter.favorited').removeClass('hidden').text(stat.favorited);
                    }

                    if (stat.favoritedMe > 0) {
                        $('.notif-counter.favoritedMe, .menu-num.favoritedMe, .counter.favoritedMe').removeClass('hidden').text(stat.favoritedMe);
                    }

                    if (stat.blacklisted > 0) {
                        $('.notif-counter.blacklisted, .menu-num.blacklisted').removeClass('hidden').text(stat.blacklisted);
                    }

                    if (stat.users_online > 0) {
                        $('.notif-counter.usersOnline').removeClass('hidden').text(stat.users_online);
                    }
                    if (stat.newUsers > 0) {
                        $('.notif-counter.usersOnline').removeClass('hidden').text(stat.newUsers);
                    }

                }
            }
        });
    }
    getDataFromServerTimeout = setTimeout(function () {
        getDataFromServer();
    }, 30000)

}


function resendSms() {

    $.ajax({
        url: '/activation/send',
        type: 'post',
        data: {'repeat': true},
        success: function (res) {
            console.log(res);
            $('.change-phone').modal('hide');
            $('.smsReset').parent('.row').addClass('hidden');
            alert(res.text)
        }
    });

}

function resendEmail() {

    $.ajax({
        url: '/activation/sendEmail',
        type: 'post',
        data: {'repeat': true},
        success: function (res) {
            console.log(res);
            $('.change-email').modal('hide');
            $('.smsReset').parent('.row').addClass('hidden');
            alert(res.text)
        }
    });

}
