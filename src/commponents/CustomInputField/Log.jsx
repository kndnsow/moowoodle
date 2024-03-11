import React, { useState, useEffect } from "react";
import axios from 'axios';

const Log = () => {
    const [logs, setLogs] = useState([]);
    const handleClearLog = async (event) => {
      if (!window.confirm("Are you sure?")) {
        event.preventDefault();
      } else {
        await clearLogs();
        await loadLogs();
      }
    };
  
    const clearLogs = async () => {
      // Implement logic to clear logs on the server
    };
  
    const fetchLogs = async () => {
      try {
        const response = await axios.get(
            `${MooWoodleAppLocalizer.rest_url}moowoodle/v1/fetch-mw-log`,
            {
            headers: { "X-WP-Nonce": MooWoodleAppLocalizer.nonce },
            }
        );
        return response.data;
      } catch (error) {
        console.error("Error fetching logs:", error);
        return [];
      }
    };
  
    const loadLogs = async () => {
      const logsData = await fetchLogs();
      setLogs(logsData);
    };
  
    useEffect(() => {
      loadLogs();
    }, []); // Fetch logs on component mount
  
    return (
      <div className="mw-input-content">
        <div className="mw-log-content">
          <form method="post" onSubmit={handleClearLog}>
            <button type="submit" className="button-secondary">
              Clear Log
            </button>
          </form>
          <div className="mw-log-status">
            {logs.map((log, index) => (
              <p key={index}>{log}</p>
            ))}
          </div>
        </div>
      </div>
    );
};

export default Log;
