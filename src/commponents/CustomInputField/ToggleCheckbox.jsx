import React from 'react';

const ToggleCheckbox = (props) => {
    const { field } = props;
    console.log(field.preSetting)
    return (
        <>
            <div className='mw-toggle-checkbox-content'>
                <input
                    id={field.id}
                    className={field.id}
                    name={field.name}
                    type="checkbox"
                    defaultChecked={field.preSetting?.[field.name]}
                    onChange={(e) => {
                        props.onChange?.(e);
                    }}
                    disabled={field.is_pro === 'pro' && MooWoodleAppLocalizer.porAdv}
                />
                <label htmlFor={field.id} className='mw-toggle-checkbox-label'></label>
            </div>
        </>
    );
};

export default ToggleCheckbox;
