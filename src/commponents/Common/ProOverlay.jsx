import React from 'react';

const ProOverlay = () => {
	return (
		<>
			<div className="mw-image-overlay">
                <div className="mw-overlay-content">
                <span className="dashicons dashicons-no-alt mw-modal cross"></span>
                <h1 className="banner-header">
                    Unlock <span className="banner-pro-tag">Pro</span>{" "}
                </h1>
                <h2>Upgrade to Moowoodle Pro</h2>
                <div className="mw-banner-content">
                    Boost to MooWoodle Pro to access premium features and enhancements!
                    <p> </p>
                    <p>1. Convenient Single Sign-On for Moodle™ and WordPress Login.</p>
                    <p>2. Create steady income through course subscriptions.</p>
                    <p>3. Increase earnings by offering courses in groups, variations, or individually.</p>
                    <p>4. Selectively sync courses with flexibility.</p>
                    <p>5. Effortlessly synchronize courses in bulk.</p>
                    <p>6. Automatic User Synchronization for Moodle™ and WordPress.</p>
                    <p>7. Choose which user information to synchronize.</p>
                </div>
                <div className="mw-banner-offer">Today's Offer</div>
                <div className="discount-tag">
                    Upto <b>15%</b>Discount
                </div>
                <p className="">
                    Seize the opportunity – upgrade now and unlock the full potential of our Pro features with a 15% discount using coupon code:
                    <span className="mw-cupon">UP15!</span>
                </p>
                <a className="mw-go-pro-btn" target="_blank" href={MooWoodleAppLocalizer.shop_url}>
                    Buy MooWoodle Pro
                </a>
                </div>
            </div>
		</>
	);
}
export default ProOverlay;
