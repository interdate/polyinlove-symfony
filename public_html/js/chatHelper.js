'use strict';
console.log('helper');
if ($('#currentUserId').length > 0 && $('#contactId').length > 0) {
    var peer, peerConnection, peerConnectionApp;
    var user_id = 'polyinlove' + $('#currentUserId').val() + '_' + $('#contactId').val();
    var user_to = 'polyinlove' + $('#contactId').val() + '_' + $('#currentUserId').val();
    var user_to_app = 'polyinloveApp' + $('#contactId').val() + '_' + $('#currentUserId').val();
    var helperAnswer;
    var canReadMessages = (typeof dialog == 'object') ? dialog.allowedToReadMessage : false;
    if (![$('#currentUserId').val(), $('#contactId').val()].includes('3745')) {
        canReadMessages = true;
    }
    var hasPoints = $('#points').val() > 0;
    document.addEventListener('load', function(e) {
        if ($('#currentUserId').length > 0 && $('#contactId').length > 0) {
            init();
        }
    }, true);
}
function init(newId=false) {
    if (typeof peer == 'undefined') {
        peer = new Peer(user_id);
        console.log(user_id);
        waitPeer();
    }
}
function waitPeer() {
    if (typeof peer == 'object') {
        if (typeof peer.on == 'undefined') {
            peer = new Peer(user_id);
            waitPeer();
        } else {
            peer.on('open', function(id) {
                console.log('My peer ID is: ' + id);
                start();
            });
            peer.on('connection', function(conn) {
                $('.unread').removeClass('unread').removeClass('sent').addClass('read')
                console.log(conn['peer']);
                if (user_to == conn['peer']) {
                    peerConnection = conn;
                    peerConnection.on('data', function(data) {
                        console.log('Received ', data);
                        helperAnswer = data;
                        insideMessages();
                    });
                } else if (user_to_app == conn['peer']) {
                    peerConnectionApp = conn;
                    peerConnectionApp.on('data', function(data) {
                        console.log('Received ', data);
                        helperAnswer = data;
                        insideMessages();
                    });
                }
                console.log(peerConnection);
            });
            peer.on('error', function(err) {
                console.log(err.type);
            });
            peer.on('disconnected', function(e) {
                console.log('diconnect');
                peerConnection = null;
                start();
            });
        }
    }
}
function start() {
    if (typeof peerConnection != 'object') {
        if (typeof peer == 'undefined') {
            init();
        }
        peerConnection = peer.connect(user_to);
        peerConnection.on('data', function(data) {
            console.log('Received 2', data);
            helperAnswer = data;
            insideMessages();
        });
    }
    if (typeof peerConnectionApp != 'object') {
        if (typeof peerConnectionApp == 'undefined') {
            waitPeer();
        } else {
            peerConnectionApp = peer.connect(user_to_app);
            peerConnectionApp.on('data', function(data) {
                console.log('Received 2', data);
                helperAnswer = data;
                insideMessages();
            });
        }
    }
}
function helperSend(message) {
    console.log('in helper', message)
    if (typeof peerConnection == 'object') {
        if (typeof peerConnection.send != 'undefined') {
            console.log('send')
            peerConnection.send(message);
        } else {
            console.log('no connection')
            start();
            helperSend(message);
        }
    }
    if (typeof peerConnectionApp == 'object') {
        if (typeof peerConnectionApp.send != 'undefined') {
            console.log('send')
            peerConnectionApp.send(message);
        } else {
            console.log('no connection')
            start();
            helperSend(message);
        }
    }
}
function insideMessages() {
    console.log('insideMessages')
    var data = JSON.parse(helperAnswer);
    console.log(data);
    console.log();
    if (data.action == 'new') {
        console.log('new');
        if (!canReadMessages) {
            console.log('!can');
            data.text = 'Cannot read messages without a subscription. Click here: <a href="/user/subscription">to purchase a subscription</a>';
            console.log(Messenger.currentUserHasPoints);
            if (hasPoints) {
                data.text += ' or <a onclick="Messenger.useFreePointToReadMessage(this)"> use a point.</a>';
            }
        }
        if ($('#dialogMessageSection_' + data.id).length == 0) {
            dialog.insertMessage(data);
            if (canReadMessages) {
                dialog.setMessageAsRead(data);
            }
        }
        console.log('INSERT NEW');
        setTimeout(function() {
            $('#dialogMessageSection_' + data.id + ',.dialogMessageSection_' + data.id).removeClass('sent').removeClass('unread').removeClass('read');
        }, 100);
        if (canReadMessages) {
            data.action = 'read';
            helperSend(JSON.stringify(data));
        }
    }
    if (data.action == 'read') {
        $('#dialogMessageSection_' + data.id + ',.dialogMessageSection_' + data.id).removeClass('sent').removeClass('unread').addClass(Messenger.messageStatus.read);
        Messenger.deleteUnreadMessageId(data.id);
    }
    helperAnswer = '{}';
}
// 'use strict';
//
// var peer, peerConnection, peerConnectionApp;
// var user_id = 'poly' + $('#currentUserId').val() + '_' + $('#contactId').val();
// var user_to = 'poly' + $('#contactId').val() + '_' + $('#currentUserId').val();
// var user_to_app = 'polyApp' + $('#contactId').val() + '_' + $('#currentUserId').val();
// var helperAnswer;
//
// if($('#currentUserId').length > 0 && $('#contactId').length > 0) {
//     window.addEventListener('DOMContentLoaded', (event) => {
//         init();
//     });
// }
//
// function init(newId = false){
//     peer = new Peer(user_id, {
//         host: "peerjs.wee.co.il",
//         port: 9000,
//         path: '/peerjs',
//         // debug: 3
//     });
//     console.log(user_id);
//     waitPeer();
//     setTimeout(function (){
//         start();
//     },500);
// }
//
// function waitPeer(){
//     if(typeof peer == 'object'){
//         if(typeof peer.on == 'undefined'){
//             peer = new Peer(user_id, {
//                 host: "peerjs.wee.co.il",
//                 port: 9000,
//                 path: '/peerjs',
//                 // debug: 3
//             });
//             waitPeer();
//             start();
//         }else{
//             //
//             peer.on('open', function(id) {
//                 console.log('My peer ID is: ' + id);
//             });
//             peer.on('connection', function(conn) {
//                 // alert(1)
//
//                 $('.unread').removeClass('unread').removeClass('sent').addClass('read')
//
//
//                 console.log(conn.peer);
//
//                 if (user_to == conn.peer) {
//                     peerConnection = conn;
//                     peerConnection.on('data', function(data) {
//                         // alert(2)
//                         console.log('Received ', data);
//                         helperAnswer = data;
//                         insideMessages();
//                     });
//                 } else if(user_to_app == conn.peer) {
//                     peerConnectionApp = conn;
//                     peerConnectionApp.on('data', function(data) {
//                         // alert(2)
//                         console.log('Received ', data);
//                         helperAnswer = data;
//                         insideMessages();
//                     });
//                 }
//
//                 console.log(peerConnection);
//             });
//             peer.on('error', function(err){
//                 //console.log(err);
//                 console.log(err.type);
//                 //start();
//                 // if(err.type == 'unavailable-id') {
//                 //     init($('#currentUserId').val() + 'poly');
//                 //     tempId = true;
//                 // }
//                 // if(err.type == 'peer-unavailable'){
//                 //     start();
//                 // }
//             });
//             peer.on('disconnected', function (e) {
//                 console.log('diconnect');
//                 peerConnection = null;
//                 start();
//             });
//         }
//     }
// }
//
// function start(){
//     // if(tempId == true){
//     //     peerConnection = peer.connect(user_id);
//     //     setTimeout(function () {
//     //         var message = {
//     //             action: 'disconnect',
//     //             text: "Take of from my Id"
//     //         };
//     //         helperSend(JSON.stringify(message));
//     //     },1500);
//     //     // peerConnection.on('data', function (data) {
//     //     //     console.log('Received', data);
//     //     // });
//     //     //
//     //     setTimeout(function () {
//     //         tempId = false;
//     //         peer.destroy();
//     //         peer = null;
//     //         init();
//     //     },3500);
//     //
//     // }
//     if(typeof peerConnection != 'object'){
//         console.log('peer connection  = ' )
//         peerConnection = peer.connect(user_to);
//         console.log({peerConnection})
//         peerConnection.on('data', function (data) {
//             console.log('Received 2', data);
//             helperAnswer = data;
//             insideMessages();
//         });
//
//     }
//
//     if(typeof peerConnectionApp != 'object'){
//         peerConnectionApp = peer.connect(user_to_app);
//         console.log(peerConnectionApp)
//         peerConnectionApp.on('data', function (data) {
//             console.log('Received 2', data);
//             helperAnswer = data;
//             insideMessages();
//         });
//
//     }
//     // if(typeof conOnMy != 'object' && tempId == false) {
//     //     conOnMy = peer.connect(user_to);
//     //     conOnMy.on('data', function (data) {
//     //         console.log('Received', data);
//     //         helperAnswer = data;
//     //         insideMessages();
//     //     });
//     // }
// }
//
// function helperSend(message){
//     if(typeof peerConnection == 'object') {
//         if (typeof peerConnection?.send != 'undefined') {
//             peerConnection.send(message);
//         }else{
//             //alert('no connection');
//             start();
//             helperSend(message);
//         }
//     }else{
//         start();
//         helperSend(message);
//     }
//     if(typeof peerConnectionApp == 'object') {
//         if (typeof peerConnectionApp?.send != 'undefined') {
//             peerConnectionApp.send(message);
//         }else{
//             //alert('no connection');
//             start();
//             helperSend(message);
//         }
//     }else{
//         start();
//         helperSend(message);
//     }
// }
//
// function insideMessages() {
//     var data = JSON.parse(helperAnswer);
//     if(data.action == 'new'){
//             dialog.insertMessage(data);
//             //if (dialog.allowedToReadMessage) {
//                 dialog.setMessageAsRead(data);
//             //}
//         console.log('INSERT NEW');
//         setTimeout(function () {
//             $('#dialogMessageSection_' + data.id + ',.dialogMessageSection_' + data.id)
//                 .removeClass('sent')
//                 .removeClass('unread')
//                 .removeClass('read')
//             ;
//         },100);
//
//
//         data.action = 'read';
//         helperSend(JSON.stringify(data));
//     }
//     if(data.action == 'read'){
//
//         $('#dialogMessageSection_' + data.id + ',.dialogMessageSection_' + data.id)
//             .removeClass('sent')
//             .removeClass('unread')
//             .addClass(Messenger.messageStatus.read)
//         ;
//
//         Messenger.deleteUnreadMessageId(data.id);
//     }
//     // if(data.action == 'disconnect'){
//     //     peer.destroy();
//     //     peer = null;
//     // }
//     helperAnswer = '{}';
// }
