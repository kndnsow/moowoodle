import React from 'react';

const SideBanner = () => {
	return (
		<>
			<div class="mw-sidebar">
                <div class="mw-banner-right">
                    <a class="mw-image-adv">
                    <img src={MooWoodleAppLocalizer.side_banner_img} />
                    </a>
                    <div class="mw-banner-right-content">
                        Unlock premium features and elevate your experience by upgrading to MooWoodle Pro!
                        <div class="mw-banner-line"> With our Pro version, you can enjoy:</div>
                        <p>&nbsp;</p>
                        <p><span>1.</span> Convenient Single Sign-On for Moodle™ and WordPress Login</p>
                        <p><span>2.</span> Create steady income through course subscriptions.</p>
                        <p><span>3.</span> Increase earnings by offering courses in groups, variations, or individually.</p>
                        <p><span>4.</span> Select and sync courses with flexibility</p>
                        <p><span>5.</span> Easily synchronize courses in bulk</p>
                        <p><span>6.</span> Seamless, One-Password Access to Moodle™ and WordPress.</p>
                        <p><span>7.</span> Choose which user information to synchronize.</p>
                        <p><span>8.</span> Automatic User Synchronization for Moodle™ and WordPress.</p>
                        <p class="supt-link">
                        <a href="<?php echo esc_url(MOOWOODLE_SUPPORT_URL) ?>" target="_blank">
                            Got a Support Question
                        </a>
                        <i class="fas fa-question-circle"></i>
                        </p>
                    </div>
                </div> 
            </div>
		</>
	);
}
export default SideBanner;
