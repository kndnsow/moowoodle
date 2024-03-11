import React, { useState } from 'react';
import axios from 'axios';
const Button = (props) => {
  let course_id = '';
  let user_id = '';
  let message = '';
  const [emptyDivContaint, setEmptyDivContaint] = useState('');
  const handleButtonClick = async (event, route) => {
    event.target.classList.add("active");
    
    console.log(event.currentTarget.parentElement.parentElement.querySelector('.test-connection-contains'))
    switch (route) {
      case "test-connection":
        await testCunnection(Object.keys(MooWoodleAppLocalizer.testconnection_actions), MooWoodleAppLocalizer.testconnection_actions, route, message );
        event.target.classList.remove("active");
        break;
      case "sync-course-options":
        await syncCourse(route);
        break;
      case "sync-all-user-options":
        await syncUser();
        break; // Add case with fieldid if any button is added.
      default:
        
    }
  };
  const testCunnection = async(actions, actions_desc, route, message) => {
    const action = actions.shift();
    if(!action)return;
    const data = {
      action: action,
      user_id: user_id,
      course_id: course_id,
    };
    try {
      const response = await handleAxios(data, route);
      if (response.data.message) {
        message = (
          <>
          {message}
          <div className="test-connection-status">
            <span className="test-connection-status-content">{actions_desc[action]} :</span>
            <span className="test-connection-status-icon">
              {response.data.message === 'success' ? (
                <i className="mw-success-icon dashicons dashicons-yes-alt"></i>
              ) : (
                <>
                  <i className="mw-error-icon dashicons dashicons-dismiss"></i>
                  <span dangerouslySetInnerHTML={{ __html: response.data.message}} ></span>
                </>
              )}
            </span>
          </div>
          </>
        );
        setEmptyDivContaint( () => {
          return (message);
        });
        user_id= response.data.user_id;
        course_id= response.data.course_id;
        console.log(response.data);
        if (response.data.course_empty) {
            console.log(response.data.course_empty);
        }
        if (action === 'get_site_info' && response.data.message !== 'success') {
            console.log('Setup Problem.');
        } else if (action === 'update_user' && !course_id) {
            console.log('Course not found.');
        } else if (action === 'update_user' && response.data.user_id === null) {
            console.log('User not found.');
        } else if (action) {
            await testCunnection(actions, actions_desc, route, message);
        }
      }
    }catch(error){
      console.log(error);
    };
  }
  const syncCourse = async(route) => {
    console.log(props.field.preSetting)
    try {
      await handleAxios({preSetting: props.field.preSetting}, route);
    } catch(error){
      console.log(error);
    };
  }
  const syncUser = () => {}

  const handleAxios = async(data, route) => {
    try {
      const response = await axios({
        method: 'post',
        url: `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/${route}`,
        headers: { "X-WP-Nonce": MooWoodleAppLocalizer.nonce },
        data: {
          data: data,
        }
      })
      return response;
    }catch(error){
      console.log(error);
    };
  };
  return (
    <>
      {
        props.emptyDiv && <div class={props.fieldid + "-contains"}>{emptyDivContaint}</div>
      }
      <p class="mw-save-changes">
        <button
          class={`button-primary ${
            props.field.is_pro ? "mw-pro-popup-overlay" : ""
          } ${props.fieldid}`}
          type="button"
          onClick={(e) => {
            handleButtonClick(e, props.fieldid);
          }}
        >{props.field.submit_btn_value}<span class="load loading"></span></button>
      </p>
    </>
  );
};
export default Button;
