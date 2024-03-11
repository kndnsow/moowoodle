import React from 'react';
import { useLocation } from 'react-router-dom';
import AllCourses from "./commponents/SubMenuPage/AllCourses";
import ManageEnrolment from "./commponents/SubMenuPage/ManageEnrolment";
import Settings from "./commponents/SubMenuPage/Settings";
import Synchronization from "./commponents/SubMenuPage/Synchronization";
import SideBanner from "./commponents/Common/SideBanner";
const App = () => {
    const currentUrl = window.location.href;
        document.querySelectorAll('#toplevel_page_moowoodle>ul>li>a').forEach((element) => {
            element.parentNode.classList.remove('current');
            if (currentUrl.includes(element.href)) {
                element.parentNode.classList.add('current');
            }
        });
    const location = new URLSearchParams(useLocation().hash);
    // console.log('sub ' + location.get('sub-tab'));
	return (
		<>

        {/* <Header /> */}
        <div class="mw-header-wapper">MooWoodle</div>
        <div class="mw-container">
            <div class="mw-middle-container-wrapper mw-horizontal-tabs">
                    { location.get('tab') === 'moowoodle-all-courses' && <AllCourses /> }
                    { location.get('tab') === 'moowoodle-manage-enrolment' && <ManageEnrolment /> }
                    { location.get('tab') === 'moowoodle-settings' && <Settings /> }
                    { location.get('tab') === 'moowoodle-synchronization' && <Synchronization /> }
                {/* <?php if ($layout == '2-col'): ?> */}
                <SideBanner />
                {/* <?php endif;?> */}
            </div>
	    </div>
        
		</>
	);
}
export default App;
