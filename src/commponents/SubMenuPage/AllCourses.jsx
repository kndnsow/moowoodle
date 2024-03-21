import React, { useState, useEffect } from "react";
import axios from "axios";
import Tabs from "./../Common/Tabs";
import logo from "./../../assets/images/logo-moowoodle-pro.png";
import DataTable from 'react-data-table-component';
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
  
const AllCourses = () => {
  const { __ } = wp.i18n;
  const [successMsg, setSuccessMsg] = useState('');
  const [courses, setCourses] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [loading, setLoading] = useState(true);

  const columns = [
    {
      name: __('Course Name'),
      selector: row => row.course_name,
      cell: (row) => (
        <a href={row.moodle_url}>
          {row.course_name}
        </a>
      ),
      sortable: true,
    },
    {
      name: __('Short Name'),
      selector: row => row.course_short_name,
      sortable: true,
    },
    {
      name: 'Product Name',
      selector: row => row.product,
      cell: (row) => (
        Object.keys(row.product).length !== 0 ? (
          Object.entries(row.product).map(([productName, productURL], index) => (
           <>
            <a key={index} href={productURL}>
              {productName}
            </a><br/>
           </>
          ))
        ) : (
          '-'
        )
      ),
      sortable: true,
    },
    {
      name: __('Category Name'),
      selector: row => row.catagory_name,
      cell: (row) => (
        <a href={row.catagory_url}>
          {row.catagory_name}
        </a>
      ),
      sortable: true,
    },
    {
      name: __('Enrolled Users'),
      selector: row => row.enroled_user,
      sortable: true,
    },
    {
      name: __('Date'),
      selector: row => row.date,
    },
    {
      name:<div dangerouslySetInnerHTML={{__html: __('Actions') + MooWoodleAppLocalizer.pro_sticker}}></div>,
      selector: row => row.course_name,
      cell: (row, rowIndex) => (
        <div>
          <div class="moowoodle-course-actions">
              <button
                type="button"
                name="sync_courses"
                class= {`${MooWoodleAppLocalizer.pro_popup_overlay} sync-single-course button-primary`}
                title={__('Sync Couse Data')}
                onClick={ (e) => {
                  handleSingleSyncCourse(
                    'sync_courses',
                    row.moodle_course_id,
                    row.id,
                    rowIndex
                  ) 
                }}
                >
                <i class="dashicons dashicons-update"></i>
              </button>
              {
                Object.keys(row.product).length !== 0 ?
                <button
                 type="button"
                 name="sync_update_product"
                 class={`${MooWoodleAppLocalizer.pro_popup_overlay} update-existed-single-product button-secondary `}
                 title={__('Sync Course Data & Update Product')}
                 onClick={ (e) => {
                  handleSingleSyncCourse(
                    'sync_update_product',
                    row.moodle_course_id,
                    row.id,
                    rowIndex
                  ) 
                 }}
                 >
                  <i class="dashicons dashicons-admin-links"></i>
                </button>
                :
                <button
                 type="button"
                 name="sync_create_product"
                 class={`${MooWoodleAppLocalizer.pro_popup_overlay} create-single-product button-secondary`}
                 title={__('Create Product')}
                 onClick={ (e) => {
                  handleSingleSyncCourse(
                    'sync_create_product',
                    row.moodle_course_id,
                    row.id,
                    rowIndex
                  )
                 }}
                >
                  <i class="dashicons dashicons-cloud-upload"></i>
                </button>
              }
            </div>
        </div>
      ),
    },
  ];
  useEffect(() => {
    // Fetch data from the WordPress REST API
    const fetchData = async () => {
      try {
        const response = await axios.get(
          `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/fetch-all-courses`,
          {
            headers: { "X-WP-Nonce": MooWoodleAppLocalizer.nonce },
          }
        );
        setCourses(response.data);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching data:", error);
        setLoading(false);
      }
    };
    
    fetchData();
  }, []);
  const handleSingleSyncCourse = async (action, moodleCourseIds, CourseIds,rowIndex) => {
    if(!MooWoodleAppLocalizer.porAdv){
      axios({
        method: 'post',
        url: `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/all-course-bulk-action`,
        headers: { 'X-WP-Nonce' : MooWoodleAppLocalizer.nonce },
        data: {
          selectedAction: action,
          moodleCourseIds: [moodleCourseIds],
          CourseIds: [CourseIds]
        },
      }).then((response) => {
        const updatedCourses = [...courses];
        console.log(updatedCourses)
        updatedCourses[rowIndex] = response.data[0];
        console.log(updatedCourses)
        setCourses(updatedCourses);
          setSuccessMsg("Synced");
          setTimeout(() => {
              setSuccessMsg('');
          }, 2050);
      }).catch((error) => {
          console.error('Error:', error);
      });
    }
  };
  const handleSelectedRowsChange = ( selecteRowsData ) => {
    // You can set state or dispatch with something like Redux so we can use the retrieved data
    setSelectedRows(selecteRowsData.selectedRows);
  };
  
  const handleBulkAction = async () => {
    // Get the selected option from the dropdown
    const selectedAction = document.getElementById("bulk-action-selector-top").value;
    console.log('Selected action:', selectedAction);
    const CourseIds = selectedRows.map(row => row.id);
    // Extract moodle_course_id from selectedRows
    const moodleCourseIds = selectedRows.map(row => row.moodle_course_id);
    console.log(moodleCourseIds);
    
    if(!MooWoodleAppLocalizer.porAdv){
      axios({
        method: 'post',
        url: `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/all-course-bulk-action`,
        headers: { 'X-WP-Nonce' : MooWoodleAppLocalizer.nonce },
        data: {
          selectedAction: selectedAction,
          moodleCourseIds: moodleCourseIds,
          CourseIds: CourseIds
        },
      }).then((response) => {
      const updatedCourses = courses.map(course => {
        const updatedCourse = response.data.find(data => data.moodle_course_id === course.moodle_course_id);
        if (updatedCourse) {
          return updatedCourse; // Replace the course data with updatedCourse data
        } else {
          return course; // If not found, keep the original course data
        }
      });
      setCourses(updatedCourses)
      console.log(updatedCourses)
        // setCourses(updatedCourses);
          setSuccessMsg("Synced");
          setTimeout(() => {
              setSuccessMsg('');
          }, 2050);
      }).catch((error) => {
          console.error('Error:', error);
      });
    }
  }


  return (
    <>
      <div class="mw-middle-child-container">
        <Tabs />
        <div class="mw-tab-content">
          <div class="mw-dynamic-fields-wrapper">
            <form class="mw-dynamic-form" action="options.php" method="post">
            {
                successMsg &&
                <div className="mw-notic-display-title setting-display">
                    <i className="mw-font dashicons dashicons-saved"></i>
                    { successMsg }
                </div>
            }
              <div id="moowoodle-link-course-table" class="mw-section-wraper">
                <div class="mw-section-child-wraper">
                  <div class="mw-header-search-wrap">
                    <div class="mw-section-header">
                      <h3>{__('Courses')}</h3>
                    </div>
                  </div>
                  <div class="mw-section-containt">
                    <div class="mw-form-group">
                      <div className="mw-input-content">
                        <div className="mw-course-table-content ">
                          <div className="moowoodle-table-fuilter"></div>
                          <div className="search-bulk-action">
                            <div
                              className={`${MooWoodleAppLocalizer.pro_popup_overlay} mw-filter-bulk`}
                            >
                              <label
                                htmlFor="bulk-action-selector-top"
                                className="screen-reader-text"
                              >
                                {__('Select bulk action')}
                              </label>
                              <select
                                name="action"
                                id="bulk-action-selector-top"
                              >
                                <option value="-1">
                                  {__('Bulk Actions')}
                                </option>
                                <option value="sync_courses">
                                  {__('Sync Course')}
                                </option>
                                <option value="sync_create_product">
                                  {__('Create Product')}
                                </option>
                                <option value="sync_update_product">
                                  {__('Update Product')}
                                </option>
                              </select>
                              <button
                                className={`button-secondary bulk-action-select-apply ${MooWoodleAppLocalizer.pro_popup_overlay}`}
                                name="bulk-action-apply"
                                type="button"
                                onClick={handleBulkAction}
                              >
                                {__('Apply',)}
                              </button>
                              <div
                                dangerouslySetInnerHTML={{
                                  __html: MooWoodleAppLocalizer.pro_sticker,
                                }}
                              ></div>
                            </div>
                            <div class="mw-header-search-section">
                              <label class="moowoodle-course-search">
                                <i class="dashicons dashicons-search"></i>
                              </label><input type="search" class="moowoodle-search-input" placeholder="Search Course" aria-controls="moowoodle_table"></input>
                            </div>
                          </div>
                        </div>
                        
                        <DataTable
                          columns={columns}
                          data={courses}
                          selectableRows
                          onSelectedRowsChange={handleSelectedRowsChange}
                          progressPending={loading}
                          progressComponent={<LoadingSpinner />}
                        />
                        <br />
                        <p className="mw-sync-paragraph">
                          {__('Cannot find your course in this list?')}
                          <a
                            href={`${MooWoodleAppLocalizer.admin_url}admin.php?page=moowoodle#&tab=moowoodle-synchronization&sub-tab=moowoodle-sync-now`}
                          >
                            {__('Synchronize Moodle Courses from here.')}
                          </a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </>
  );
};
export default AllCourses;
