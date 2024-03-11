import React from "react";
const TextBox = (props) => {
  const { field } = props;
  return (
    <>
      <div class="mw-textbox-input-wraper regular-text">
        <input
          id={field.id}
          class={
            "mw-setting-form-input " + field.is_pro &&
            MooWoodleAppLocalizer.porAdv &&
            "disabled"
          }
          name={field.name}
          type="text"
          value={field.preSetting ? field.preSetting?.[field.name] : ''}
          onChange={(e) => { props.onChange?.(e) }}
        ></input>
        {field.copy_text == "copy" && (
          <button class="mw-copytoclip button-secondary" type="button">
            Copy
          </button>
        )}
      </div>
    </>
  );
};
export default TextBox;
