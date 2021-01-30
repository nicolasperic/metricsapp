/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');//nothing works without it

//window.Vue = require('vue');
window.Pusher = require('pusher-js');
import Echo from "laravel-echo";

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '4c587c5bccbaa41d9f62',
    cluster: 'mt1',
    encrypted: true
});

$(document).ready(function() {
    if(Laravel.userId) {

        window.Echo.private(`App.User.${Laravel.userId}`)
    .notification((notification) => {
            addNotifications([notification], '#notifications');
            $('#alertsDropdown').dropdown('toggle');

        //custom code only for Reports Index page (updating report data dynamically)
        if ($('#report-'+notification.data.entity_id).length) {
            var to = 'reports/' + notification.data.entity_id;
            var toLink = '<a href="'+to+'" target="_blank">View<a/>'
            $("#report-"+notification.data.entity_id+" td.report-status").html('Processed');
            $("#report-"+notification.data.entity_id+" td.report-link").html(toLink);
        }

    });
}
});



var notifications = [];

const NOTIFICATION_TYPES = {
    report_processed: 'App\\Notifications\\ReportProcessed',
    assembla_synced: 'App\\Notifications\\AssemblaSynced'
};

$(document).ready(function() {
    // check if there's a logged in user
    if(Laravel.userId) {
        $.get('/notifications', function (data) {
            addNotifications(data, "#notifications");
        });
    }
});

function addNotifications(newNotifications, target) {
    notifications = newNotifications.concat(notifications);
    // show only last 5 notifications
    notifications.slice(0, 5);
    showNotifications(notifications, target);
}

function showNotifications(notifications, target) {
    if(notifications.length) {
        var htmlElements = notifications.map(function (notification) {
            return makeNotification(notification);
        });
        $(target + 'Menu').html(htmlElements.join(''));
        $('#user-notifications').html(htmlElements.join(''));

        $(target).addClass('has-notifications')

        $('#notifications-counter').html(notifications.length+'+');
        $('#notifications-counter').addClass('badge-danger');
        $('#mark-notifications-as-read').show();
    } else {
        $('#notifications-counter').html('');
        $('#user-notifications').html(noNotificationsMessage());
        $('#notifications-counter').removeClass('badge-danger');
    }
}

// Make a single notification string
function makeNotification(notification) {
    var to = routeNotification(notification);
    var notificationText = makeNotificationText(notification, to);
    return notificationText;
}

// get the notification route based on it's type
function routeNotification(notification) {
    var to = '?read=' + notification.id;
    if(notification.type === NOTIFICATION_TYPES.report_processed) {
        to = 'reports/' + notification.data.entity_id + to;
    } else if(notification.type === NOTIFICATION_TYPES.assembla_synced) {
        to = notification.data.url + to;
    }
    return to;
}

// get the notification text based on it's type
function makeNotificationText(notification, url) {
    var text = '';
    if(notification.type === NOTIFICATION_TYPES.report_processed) {
        //const name = notification.data.follower_name;
        text += '<a class=\"dropdown-item d-flex align-items-center\" href=\"'+url+'\"><div class=\"mr-3\"><div class=\"icon-circle '+notification.data.bg_class+'\"><i class=\"fas '+notification.data.icon_class+' text-white\"></i></div></div><div><div class=\"small text-gray-500\">'+notification.data.date+'</div><span class=\"font-weight-bold\">'+notification.data.message+'</span></div></a>';
    } else if(notification.type === NOTIFICATION_TYPES.assembla_synced) {
        text += '<a class=\"dropdown-item d-flex align-items-center\" href=\"'+url+'\"><div class=\"mr-3\"><div class=\"icon-circle '+notification.data.bg_class+'\"><i class=\"fas '+notification.data.icon_class+' text-white\"></i></div></div><div><div class=\"small text-gray-500\">'+notification.data.date+'</div><span class=\"font-weight-bold\">'+notification.data.message+'</span></div></a>';
    }
    return text;
}

function noNotificationsMessage() {
    return '<a class=\"dropdown-item d-flex align-items-center\"><div><div class=\"small text-gray-500\">No notifications</div></div></a>';
}

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//const app = new Vue({
//    el: '#app',
//});
