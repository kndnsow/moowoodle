import React from 'react';
import Tabs from "./../Common/Tabs";
import TabContent from "./../Common/TabContent";

const Settings = () => {
	return (
        <div class="mw-middle-child-container">
            <Tabs />
            <div class="mw-tab-content">
                {/* <?php if ($layout == '2-col'): ?> */}
                <div class="mw-dynamic-fields-wrapper">
                {/* <?php endif;?> */}
                    <form class="mw-dynamic-form" action="options.php" method="post">
                        <TabContent />
                    </form>
                </div>
            </div>
        </div>
	);
}
export default Settings;
