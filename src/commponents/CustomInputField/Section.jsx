import React from "react";
const Section = (props) => {
  return (
    <>
    <div class="mw-header-search-wrap">
        <div class="mw-section-header">
            <h3>{props.label}</h3>
        </div>
    </div>
    </>
  );
};
export default Section;
