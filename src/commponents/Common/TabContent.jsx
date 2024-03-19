import { useState, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import TextBox from './../CustomInputField/TextBox';
import ToggleCheckbox from './../CustomInputField/ToggleCheckbox';
import Select from './../CustomInputField/Select';
import MultipleCheckboxs from './../CustomInputField/MultipleCheckboxs';
import Button from './../CustomInputField/Button';
import Log from './../CustomInputField/Log';
import Section from './../CustomInputField/Section';
import axios from 'axios';

const TabContent = () => {
    const { __ } = wp.i18n;
    const location = new URLSearchParams(useLocation().hash);
    const tabValue = MooWoodleAppLocalizer.library[location.get('tab')][location.get('sub-tab')];
    const [successMsg, setSuccessMsg] = useState('');
    const [Setting, setSetting] = useState(MooWoodleAppLocalizer.preSettings);
    const settingChanged = useRef(false);
    const handleChange = (event) => {
        settingChanged.current = true;
        setSetting((currentSetting) => {
            const newSetting = { ...currentSetting,  };
            if(!newSetting[tabValue.setting]){
                newSetting[tabValue.setting] = {};
            }
            console.log(event.target.type);
            if(event.target.type === 'checkbox'){
                newSetting[tabValue.setting][event.target.name] = event.target.checked;
            } else {
                newSetting[tabValue.setting][event.target.name] = event.target.value;
            }
            return newSetting;
        });
    }
    useEffect(() => {
        let timeoutId;
        timeoutId = setTimeout(() => {
            if(!tabValue) return;
            if(Setting[tabValue.setting] && settingChanged.current){
                settingChanged.current = false;
                axios({
                    method: 'post',
                    url: `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/save-moowoodle-setting`,
                    headers: { 'X-WP-Nonce' : MooWoodleAppLocalizer.nonce },
                    data: {
                        setting: Setting[tabValue.setting],
                        settingid: tabValue.setting,
                    },
                }).then((response) => {
                    setSuccessMsg("Settings Saved");
                    setTimeout(() => {
                        setSuccessMsg('');
                    }, 2050);
                }).catch((error) => {
                    console.error('Error:', error);
                });
            }
        }, 2000);
        return () => {
            clearTimeout(timeoutId);
        };
    }, [Setting]);
    const getFieldContent = (fields) => {
        if(!fields) return (
            <>{__('No souch Tab Exist.')}</>
        );
        return Object.entries(fields).map(([fieldid, field]) => {
            field['id'] = fieldid;
            field['settingid'] = tabValue.setting;
            field['preSetting'] = Setting[tabValue.setting];
            return (
                <>
                { field.type === 'section' && <Section label = {field.label} />}
                <div class={`mw-form-group ${MooWoodleAppLocalizer.proAdv ? MooWoodleAppLocalizer.pro_popup_overlay : ''}`}>
                    {field.label && field.type !== 'section' && (<label scope="row" class="mw-form-label moodle-url"><p>{field.label}</p></label>)}
                    <div class="mw-input-content">
                        {
                            field.desc_posi === 'up' &&
                            <p className='mw-form-description' dangerouslySetInnerHTML={{ __html: field.desc }}></p>
                        }
                        {field.type === 'textbox' && <TextBox field = {field} onChange={(e) => { handleChange(e) }}/>}
                        {field.type === 'toggle-checkbox' && <ToggleCheckbox field = {field} onChange={(e) => { handleChange(e) }}/>}
                        {field.type === 'select' && <Select field = {field} onChange={(e) => { handleChange(e) }}/>}
                        {field.type === 'multiple-checkboxs' && <MultipleCheckboxs field = {field} onChange={(e) => { handleChange(e) }}/>}
                        {field.submit_btn_value && <Button field = {field} fieldid = {fieldid} emptyDiv = {field.type === 'empty-div' ? true : false }/>}
                        {
                            field.desc_posi !== 'up' &&
                            <p className='mw-form-description' dangerouslySetInnerHTML={{ __html: field.desc }}></p>
                        }
                        {field.type === 'log' && <Log />}
                    </div>
                </div></>
            )
        })
    };
    
	return (
		<>
            {
                successMsg &&
                <div className="mw-notic-display-title setting-display">
                    <i className="mw-font dashicons dashicons-saved"></i>
                    { successMsg }
                </div>
            }
            <div class="mw-section-wraper">
                <div class="mw-section-child-wraper">
            { getFieldContent(tabValue?.field_types) }
                </div>
            </div>
		</>
	);
}
export default TabContent;
