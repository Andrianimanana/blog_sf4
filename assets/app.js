/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

//fontawesome-free
import '@fortawesome/fontawesome-free/js/all.js';
import '@fortawesome/fontawesome-free/css/all.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import './css/style.css';

// bootstrap
import 'bootstrap'; // here, import only js
import 'bootstrap/dist/css/bootstrap.min.css';// her import css bootstrap

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery'; 

// jquery-jscroll : scoll infini 
import 'jscroll/dist/jquery.jscroll.min.js'; 

// my script
import './js/scripts.js';