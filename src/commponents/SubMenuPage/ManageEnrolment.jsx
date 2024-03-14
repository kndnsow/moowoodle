import React, { useState, useEffect } from "react";
import axios from "axios";
import Tabs from "./../Common/Tabs";
import logo from "../../../assets/images/logo-moowoodle-pro.png";
import DataTable from 'react-data-table-component';
import { addDays } from 'date-fns';
import { DateRangePicker } from 'react-date-range';
import 'react-date-range/dist/styles.css';
import 'react-date-range/dist/theme/default.css';

const ManageEnrolment = () => {
    const [enrolment, setEnrolment] = useState([]);
    const [selectedRows, setSelectedRows] = useState([]);
    const [loading, setLoading] = useState(true);
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
            <form method="post">
                <input type="hidden" name="user_id" value={row.moowoodle_moodle_user_id} />
                <input type="hidden" name="order_id" value={row.order_id} />
                <input type="hidden" name="course_id" value={row.linked_course_id} />
                <button type="submit" onclick={`return confirm('Please confirm the decision for reenroll  ${row.user_login} to ${row.product} ?')`} name="reenroll" class="button-secondary">{row.action}</button>
            </form>
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
            <div class="mw-datepicker-wraper">
            </div>
            <div class="moowoodle-table-fuilter">
            <DateRangePicker
                onChange={item => setState([item.selection])}
                showSelectionPreview={true}
                moveRangeOnFirstSelection={false}
                months={2}
                ranges={datePicked}
                direction="horizontal"
                preventSnapRefocus={true}
                calendarFocus="backwards"
            />
            </div>
        <DataTable
            columns={columns}
            data={enrolment}
            sortable
            onSelectedRowsChange={handleSelectedRowsChange}
            progressPending={loading}
            progressComponent={<LoadingSpinner />}
        />
      </>
      );
      const handleSelectedRowsChange = ( selecteRowsData ) => {
        // You can set state or dispatch with something like Redux so we can use the retrieved data
        setSelectedRows(selecteRowsData.selectedRows);
      };
      console.log(enrolment);

	return (
		<>
			<div class="mw-middle-child-container">
                    <Tabs />
                    <div class="mw-tab-content">
                        <div class="mw-dynamic-fields-wrapper">
                            <form class="mw-dynamic-form" action="options.php" method="post">
                                <>
                                <div class='mw-manage-enrolment-content '>
                                    <div class="moowoodle-manage-enrolment  mw-pro-popup-overlay">
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
                                </>
                            </form>
                        </div>
                    </div>
                </div>
		</>
	);
}
export default ManageEnrolment;
