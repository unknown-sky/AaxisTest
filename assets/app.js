/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');

// Login 
$('#login_submit').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        url: "/api/login",
        type: "POST",
        contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify({
            "email": $('#email').val(),
            "password": $('#password').val()
        }),
        success: function(response) {
            localStorage.setItem('aaxis_token', response.token);
            window.location.href='/dashboard';
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log('Error:', textStatus, errorThrown);
        }
    });
});

// Logout 
$('#logout_button').on('click', function(e){
    e.preventDefault();

    $.ajax({
        url: "/api/logout",
        type: "POST",
        dataType: 'json',
        headers: {"X-AUTH-TOKEN": localStorage.getItem('aaxis_token')},
        success: function(response) {
            localStorage.removeItem('aaxis_token');
            window.location.href='/login';
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log('Error:', textStatus, errorThrown);
        }
    });
});

// Register
$('#register_submit').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        url: "/api/register",
        type: "POST",
        contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify({
            "email": $('#email').val(),
            "password": $('#password').val(),
            "password_confirmation": $('#password-confirm').val(),
        }),
        success: function(response) {
            localStorage.setItem('aaxis_token', response.token);
            window.location.href='/dashboard';
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log('Error:', textStatus, errorThrown);
        }
    });
});