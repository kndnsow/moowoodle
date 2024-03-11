import React, { useState, useEffect } from "react";
import axios from "axios";
import Tabs from "./../Common/Tabs";
import ProOverlay from "./../Common/ProOverlay";
import logo from "../../../assets/images/logo-moowoodle-pro.png";
import DataTable from 'react-data-table-component';
const LoadingSpinner = () => (
  <tr>
    <td
      colSpan={MooWoodleAppLocalizer.from_heading.length}
      style={{ textAlign: "center" }}
    >
      <div className="loading-spinner">
        <img className="lodaer-img-overlay" src={logo} alt="" />
      </div>
    </td>
  </tr>
);
  
const AllCourses = () => {
  const [courses, setCourses] = useState([]);
  const [selectedRows, setSelectedRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const columns = [
    {
      name: 'Course Name',
      selector: row => row.course_name,
      cell: (row) => (
        <a href={row.moodle_url}>
          {row.course_name}
        </a>
      ),
      sortable: true,
    },
    {
      name: 'Short Name',
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
      name: 'Category Name',
      selector: row => row.catagory_name,
      cell: (row) => (
        <a href={row.catagory_url}>
          {row.catagory_name}
        </a>
      ),
      sortable: true,
    },
    {
      name: 'Enrolled Users',
      selector: row => row.enroled_user,
      sortable: true,
    },
    {
      name: 'Date',
      selector: row => row.date,
    },
    {
      name: 'Actions',
      selector: row => row.course_name,
      cell: (row) => (
        <div
          dangerouslySetInnerHTML={{
            __html: row.actions,
          }}
        ></div>
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
  const handleSelectedRowsChange = ( selecteRowsData ) => {
    // You can set state or dispatch with something like Redux so we can use the retrieved data
    setSelectedRows(selecteRowsData.selectedRows);
  };
  console.log(courses);

  return (
    <>
      <div class="mw-middle-child-container">
        <Tabs />
        <div class="mw-tab-content">
          <div class="mw-dynamic-fields-wrapper">
            <form class="mw-dynamic-form" action="options.php" method="post">
              <div id="moowoodle-link-course-table" class="mw-section-wraper">
                <div class="mw-section-child-wraper">
                  <div class="mw-header-search-wrap">
                    <div class="mw-section-header">
                      <h3>Courses</h3>
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
                                {MooWoodleAppLocalizer.bulk_actions_label}
                              </label>
                              <select
                                name="action"
                                id="bulk-action-selector-top"
                              >
                                <option value="-1">
                                  {MooWoodleAppLocalizer.bulk_actions}
                                </option>
                                <option value="sync_courses">
                                  {MooWoodleAppLocalizer.sync_course}
                                </option>
                                <option value="sync_create_product">
                                  {MooWoodleAppLocalizer.create_product}
                                </option>
                                <option value="sync_update_product">
                                  {MooWoodleAppLocalizer.update_product}
                                </option>
                              </select>
                              <button
                                className={`button-secondary bulk-action-select-apply ${MooWoodleAppLocalizer.pro_popup_overlay}`}
                                name="bulk-action-apply"
                                type="button"
                              >
                                {MooWoodleAppLocalizer.apply}
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
                          {MooWoodleAppLocalizer.cannot_find_course}
                          <a
                            href={`${MooWoodleAppLocalizer.admin_url}admin.php?page=moowoodle#&tab=moowoodle-synchronization&sub-tab=moowoodle-sync-now`}
                          >
                            {MooWoodleAppLocalizer.sync_moodle_courses}
                          </a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                {MooWoodleAppLocalizer.porAdv && <ProOverlay />}
              </div>
            </form>
          </div>
        </div>
      </div>
    </>
  );
};
export default AllCourses;
