import React, { useState, useEffect } from "react";
import { useLocation } from 'react-router-dom';
import AllCourses from "./commponents/SubMenuPage/AllCourses";
import ManageEnrolment from "./commponents/SubMenuPage/ManageEnrolment";
import Settings from "./commponents/SubMenuPage/Settings";
import Synchronization from "./commponents/SubMenuPage/Synchronization";
import SideBanner from "./commponents/Common/SideBanner";
import ProOverlay from "./commponents/Common/ProOverlay";
import dualcubeLogo from "./assets/images/dualcube.png";

// css and scss file for global styling.
import "./styles/admin.css";

// utils js file for global customisation.
import "./utils/moowoodle-admin-frontend.js";

const App = () => {
    const { __ } = wp.i18n;
    const currentUrl = window.location.href;
        document.querySelectorAll('#toplevel_page_moowoodle>ul>li>a').forEach((element) => {
            element.parentNode.classList.remove('current');
            if (currentUrl.includes(element.href)) {
                element.parentNode.classList.add('current');
            }
        });
    const location = new URLSearchParams(useLocation().hash);
    const [overlayVisible, setOverlayVisible] = useState(false);

    const handleOverlayClick = (event) => {
        console.log('hi')
        if (event.target.classList.contains('mw-pro-popup-overlay')) {
            setOverlayVisible(true);
        }
    };
    const handleImageOverlayClick = () => {
        setOverlayVisible(false);
    };
    // console.log('sub ' + location.get('sub-tab'));
	return (
		<>

        {/* <Header /> */}
        <div class="mw-header-wapper">MooWoodle</div>
        <div class="mw-container"  onClick={handleOverlayClick}>
            <div class="mw-middle-container-wrapper mw-horizontal-tabs">
                    { location.get('tab') === 'moowoodle-all-courses' && <AllCourses /> }
                    { location.get('tab') === 'moowoodle-manage-enrolment' && <ManageEnrolment /> }
                    { location.get('tab') === 'moowoodle-settings' && <Settings /> }
                    { location.get('tab') === 'moowoodle-synchronization' && <Synchronization /> }
                <SideBanner />
                {!MooWoodleAppLocalizer.porAdv && <ProOverlay />}
            </div>
	    </div>
		<div class="dualcube-admin-footer" id="dualcube-admin-footer">
        {__('Powered by')} <a href={MooWoodleAppLocalizer.dualcubeUrl} target="_blank"><img src={dualcubeLogo} /></a>{__('ualCube')} &copy; { new Date().getFullYear() }
		</div>
		</>
	);
}
export default App;
