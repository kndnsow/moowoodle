import { useEffect, useState } from 'react';
import DataTable from 'react-data-table-component';
import ReactPaginate from 'react-paginate';

const TableWithPagination = ( props ) => {
  const [itemsPerPage, setItemsPerPage] = useState( props.data.length );
  
  useEffect(()=>{
	setItemsPerPage(props.data.length);
  },[props.data.length])

  // When current page is changed
  const handlePageChange = ( { selected } ) => {
    // When current page is changed currentPage = selected and itemPerPage = itemPerPage
    props.onPageChange( selected + 1, itemsPerPage );
  };

  // When perpage is changed
  const handleItemsPerPageChange = (event) => {
    setItemsPerPage( parseInt( event.target.value ) );

    // When item perPage is changed currentPage = 0 and itemPerPage = event.target.value
    props.onPageChange(1, event.target.value);
  };

  return (
    <div>
      <DataTable
        columns={props.columns}
        data={props.data}
        selectableRows={props.selectableRows}
        onSelectedRowsChange={props.onSelectedRowsChange}
        pagination={false} // Disable built-in pagination
      />
      <ReactPaginate
        pageCount={Math.ceil(props.totallength / itemsPerPage)}
        onPageChange={handlePageChange}
        containerClassName={'pagination'}
        activeClassName={'active'}
      />
      <div>
        {
          props.perPageOptions &&
          <>
            <span>Items per page: </span>
            <select value={itemsPerPage} onChange={handleItemsPerPageChange}>
              {
                props.perPageOptions.map((value)=>{
                  return <option value={value}>{value}</option>
                })
              }
            </select>        
          </>
        }
      </div>
    </div>
  );
};

export default TableWithPagination;
