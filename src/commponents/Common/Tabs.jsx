import React, { useState } from 'react';
import { BrowserRouter as Router, Link, useLocation } from 'react-router-dom';


const Tabs = () => {
    const location = new URLSearchParams(useLocation().hash);
    const tabs = MooWoodleAppLocalizer.library[location.get('tab')];
    let currentTab = location.get('sub-tab') ? location.get('sub-tab') : Object.keys(MooWoodleAppLocalizer.library[location.get('tab')])[0] ;
    const getTabs = () => {
        return Object.entries(tabs).map(([tabid , tab]) => {
            let active = '';
            
            if(currentTab == tabid ){
                active = ' nav-tab-active'
            }
            let icon = '';
            if (tab['font_class']) {
                icon =  tab['font_class'];
            }
            return (<Link
                to={MooWoodleAppLocalizer.admin_url + "admin.php?page=moowoodle#&tab=" + location.get('tab') + "&sub-tab=" + tabid}
                id={tabid}
                className={"nav-tab" + active}
                >
                <i className={"dashicons " + icon}></i> {tab.label}
                {
                    tab.is_pro && MooWoodleAppLocalizer.porAdv &&
                    <span class="mw-pro-tag">Pro</span>
                }
                </Link>)
        });
    }
	return (
			<div class="mw-current-tab-lists">
                {getTabs()}
                <a class="nav-tab moowoodle-upgrade" href={MooWoodleAppLocalizer.shop_url} target="_blank" rel="noopener noreferrer"><i class="dashicons dashicons-awards"></i>Upgrade to Pro for More Features</a>
            </div>
	);
}
export default Tabs;
