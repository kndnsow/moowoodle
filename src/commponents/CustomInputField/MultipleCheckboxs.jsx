const MultipleCheckboxs = (props) => {
    const { field } = props;
        const getChecboxes = (option_values) => {
            return Object.entries(option_values).map(([checkboxTitle, checkboxOptions]) => {
                return(<div
                    key={checkboxOptions.id}
                    className={`mw-col-50${checkboxOptions.is_pro ? ' mw-pro-popup-overlay' : ''}`}
                >
                    <div className="mw-wrap-checkbox-and-label">
                        <div className="mw-normal-checkbox-content d-flex">
                            <input
                                id={checkboxOptions.id}
                                className={`mw-toggle-checkbox ${checkboxOptions.id} ${
                                    checkboxOptions.checked === 'forced' ? ' forceCheckCheckbox' : ''
                                }`}
                                type="checkbox"
                                name={checkboxOptions.name}
                                defaultChecked={field.preSetting[checkboxOptions.name]}
                                onChange={(e) => {
                                    props.onChange?.(e);
                                }}
                            />
                            <p className="mw-settings-checkbox-description pt-0">
                                {checkboxTitle} {checkboxOptions.is_pro && MooWoodleAppLocalizer.pro_sticker}
                            </p>
                        </div>
                        <div className="mw-normal-checkbox-label">
                            <p className="mw-form-description">{checkboxOptions.desc}</p>
                        </div>
                    </div>
                </div>)
            });
        }

    return (
        <>
            <div id={`${field.id}-multiple-checkboxs`}>
                <button type="button" id="selectDeselectButton" className="button-secondary">
                    Select / Deselect All
                </button>
                <div className="mw-select-deselect-checkbox-label-marge">
                    {getChecboxes(field.option_values)}
                </div>
            </div>
        </>
    );
};

export default MultipleCheckboxs;
