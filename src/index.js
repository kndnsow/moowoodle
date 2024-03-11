import { render } from '@wordpress/element';
import { BrowserRouter} from 'react-router-dom';
import App from './app.js';

/**
 * Import the stylesheet for the plugin.
 */
// import './style/main.scss';
// Render the App component into the DOM
render(<BrowserRouter><App /></BrowserRouter>, document.getElementById('moowoodle_root'));
