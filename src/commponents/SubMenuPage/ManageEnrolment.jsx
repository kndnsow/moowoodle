import React, { useState, useEffect } from "react";
import axios from "axios";
import Tabs from "./../Common/Tabs";
import logo from "./../../assets/images/logo-moowoodle-pro.png";
import DataTable from 'react-data-table-component';
import { addDays } from 'date-fns';
import { DateRangePicker } from 'react-date-range';
import 'react-date-range/dist/styles.css';
import 'react-date-range/dist/theme/default.css';

const ManageEnrolment = () => {
    const [enrolment, setEnrolment] = useState([]);
    const [loading, setLoading] = useState(true);
    const LoadingSpinner = () => (
        <tr>
          <td
            style={{ textAlign: "center" }}
          >
            <div className="loading-spinner">
              <img className="lodaer-img-overlay" src={logo} alt="" />
            </div>
          </td>
        </tr>
      );
      useEffect(() => {
        // Fetch data from the WordPress REST API
        const fetchData = async () => {
          try {
            const response = await axios.get(
              `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/fetch-all-enrolment`,
              {
                headers: { "X-WP-Nonce": MooWoodleAppLocalizer.nonce },
              }
            );
            setEnrolment(response.data);
            setLoading(false);
          } catch (error) {
            console.error("Error fetching data:", error);
            setLoading(false);
          }
        };
        if(!MooWoodleAppLocalizer.porAdv)
        fetchData();
      }, []);
      const columns = [
        {
          name: 'Course Name',
          selector: row => row.course,
          cell: (row) => (
            <div class="mw-course">
                <div class="mw-course-title">
                    <b>{row.course}</b>
                </div>
                <p>
                    <a href={row.viewProductUrl}>View Product</a> | 
                    <a href={row.viewCourseUrl}>View Course</a> | 
                    <a href={row.viewOrderUrl}>View Order</a> 
                </p>
            </div>
          ),
          sortable: true,
        },
        {
          name: 'Product Name',
          selector: row => row.product,
          cell: (row) => (
                <a href={row.viewProductUrl}>
                  {row.product}
                </a>
              ),
          sortable: true,
        },
        {
          name: 'Student',
          selector: row => row.user_login,
          cell: (row) => (
                <a href={row.user_login}>
                  {row.user_login}
                </a>
              ),
          sortable: true,
        },
        {
          name: 'Enrolment Date',
          selector: row => new Date(row.date * 1000).toLocaleString(),
          sortable: true,
        },
        {
          name: 'Status',
          selector: row => row.status,
          sortable: true,
        },
        {
          name: 'Actions',
          selector: row => row.course_name,
          cell: (row) => (
            <button 
              type="button" 
              onClick={ (e) => {
                handleChangeEnrollment(
                  e,
                  row.user_login,
                  row.product,
                  row.moowoodle_moodle_user_id,
                  row.order_id,
                  row.linked_course_id
                  ) 
                } }
              name={ row.status === 'Unenrolled' ? 'reenroll' : 'unenroll' }
              class="button-secondary">{row.action}
            </button>
          ),
        },
      ];
      const [datePicked, setDatePicked] = useState([
        {
          startDate: new Date(),
          endDate: addDays(new Date(), 7),
          key: 'selection'
        }
      ]);
      const ManageEnrolTable = () => (
        <>
        <DataTable
            columns={columns}
            data={enrolment}
            sortable
            progressPending={loading}
            progressComponent={<LoadingSpinner />}
        />
      </>
      );
      const handleChangeEnrollment = (event, user_login, product, moowoodle_moodle_user_id, order_id, linked_course_id  ) => {
        // const moodleCourseIds = selectedRows.map(row => row.moodle_course_id);
        console.log(event.target.name);
        const data = {
            status: event.target.name,
            user_login: user_login,
            product: product,
            moowoodle_moodle_user_id: moowoodle_moodle_user_id,
            order_id: order_id,
            linked_course_id: linked_course_id
        }
        axios({
          method: 'post',
          url: `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/unenroll-reenroll`,
          headers: { 'X-WP-Nonce' : MooWoodleAppLocalizer.nonce },
          data: {
            data: data
          },
        }).then((response) => {
            setTimeout(() => {
            }, 2050);
        }).catch((error) => {
            console.error('Error:', error);
        });
      };

	return (
    <>
    <div id="moowoodle-manage-enrolment" class="mw-section-wraper">
      <div class="mw-middle-child-container">
        <Tabs />
        <div class="mw-tab-content">
          <div class="mw-dynamic-fields-wrapper">
            <form class="mw-dynamic-form" action="options.php" method="post">
              <>
                <div class="moowoodle-manage-enrolment  mw-pro-popup-overlay">
                  <div class="mw-section-child-wraper">
                    <div class="mw-header-search-wrap"><div class="mw-section-header">
                      <h3>All Enrolments</h3>
                    </div>
                      <div class="mw-header-search-section">
                        <label class="moowoodle-course-search">
                          <i class="dashicons dashicons-search"></i>
                        </label>
                        <input type="search" class="moowoodle-search-input" onChange={(e) => { console.log(e); }} placeholder="Search Course" aria-controls="moowoodle_table" />
                      </div>
                    </div>
                    <div class="mw-form-group">
                      <div class="mw-input-content">
                        <div class="mw-manage-enrolment-content ">
                          <div class="moowoodle-table-fuilter">

                          <div class="mw-datepicker-wraper">
                          </div>
                          <div class="moowoodle-table-fuilter">
                          {/* <DateRangePicker
                              onChange={item => setState([item.selection])}
                              showSelectionPreview={true}
                              moveRangeOnFirstSelection={false}
                              months={2}
                              ranges={datePicked}
                              direction="horizontal"
                              preventSnapRefocus={true}
                              calendarFocus="backwards"
                          /> */}
                          </div>
                          </div>
                          {
                            MooWoodleAppLocalizer.porAdv ?
                            <p>
                                <a class="mw-image-adv">
                                  <img src={MooWoodleAppLocalizer.manage_enrolment_img_url} />
                                </a>
                              </p>
                              :
                              <>
                                {ManageEnrolTable()}
                              </>
                          }
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </>
            </form>
          </div>
        </div>
      </div>
    </div>
    </>
	);
}
export default ManageEnrolment;
