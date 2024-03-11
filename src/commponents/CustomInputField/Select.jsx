import React, { useEffect } from 'react';

const Select = (props) => {
    const { option_values, field } = props;
    const optionsData = [];

    let i = 0;
    for (const [k, v] of Object.entries(field.option_values)) {
        if (typeof(v) == 'object') {
            if (!i) {
                optionsData.push(
                    <option key={i} value='0'>
                        Select
                    </option>
                );
                i++;
            }
            optionsData.push(
                <option
                    key={i}
                    value={i}
                    selected={Number(field.preSetting?.[field.name]) === i}
                    data-desc={v['desc']}
                >
                    {k}
                </option>
            );
            if (Number(field.preSetting?.[field.name]) === i) {
                field.sub_desc = v['desc'];
            }
        } else {
            if (!field[field.name]) {
                field[field.name] = '';
            }
            optionsData.push(
                <option key={k} value={k} selected={Number(field.preSetting?.[field.name]) === k}>
                    {v}
                </option>
            );
        }
        i++;
    }
    useEffect(() => {
        const handleSelectChange = (event) => {
            const selectedOption = event.target.options[event.target.selectedIndex];
            const description = selectedOption.getAttribute('data-desc');
            document.querySelector('.mw-normal-checkbox-label').innerHTML = description;
        };

        const selectElement = document.getElementById(field.id);
        selectElement.addEventListener('change', handleSelectChange);

        return () => {
            // Cleanup the event listener on component unmount
            selectElement.removeEventListener('change', handleSelectChange);
        };
    }, [field.id]);

    return (
        <>
            <select
                id={field.id}
                className={field.id}
                name={field.name}
                onChange={(e) => { props.onChange?.(e) }}
            >
                {optionsData}
            </select>
            {
                field.is_pro && MooWoodleAppLocalizer.porAdv &&
                <span class="mw-pro-tag">Pro</span>
            }
            <div class="mw-normal-checkbox-label" dangerouslySetInnerHTML={{ __html: field.sub_desc }}>
            </div>
        </>
    );
};

export default Select;
