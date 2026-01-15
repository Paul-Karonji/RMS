import React, { useState, useEffect } from 'react';
import { changeRequestService } from '../services/changeRequestService';
import { Plus, Filter } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import './ChangeRequests.css';

const ChangeRequests = () => {
    const [requests, setRequests] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');
    const navigate = useNavigate();

    useEffect(() => {
        fetchChangeRequests();
    }, [filter]);

    const fetchChangeRequests = async () => {
        try {
            setLoading(true);
            const params = filter !== 'all' ? { status: filter } : {};
            const response = await changeRequestService.getChangeRequests(params);
            setRequests(response.data);
        } catch (error) {
            console.error('Failed to fetch change requests:', error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusBadge = (status) => {
        const badges = {
            pending: { class: 'badge-warning', text: 'Pending' },
            approved: { class: 'badge-success', text: 'Approved' },
            rejected: { class: 'badge-error', text: 'Rejected' }
        };
        return badges[status] || badges.pending;
    };

    const getRequestTypeLabel = (type) => {
        const labels = {
            unit_price: 'Unit Price',
            unit_condition: 'Unit Condition',
            fee_structure: 'Fee Structure',
            manager_change: 'Manager Change',
            property_details: 'Property Details'
        };
        return labels[type] || type;
    };

    return (
        <div className="change-requests-page">
            <div className="page-header">
                <h1>Change Requests</h1>
                <button className="btn-primary" onClick={() => navigate('/change-requests/new')}>
                    <Plus size={16} />
                    New Request
                </button>
            </div>

            <div className="change-requests-filters">
                <button
                    className={`filter-btn ${filter === 'all' ? 'active' : ''}`}
                    onClick={() => setFilter('all')}
                >
                    All
                </button>
                <button
                    className={`filter-btn ${filter === 'pending' ? 'active' : ''}`}
                    onClick={() => setFilter('pending')}
                >
                    Pending
                </button>
                <button
                    className={`filter-btn ${filter === 'approved' ? 'active' : ''}`}
                    onClick={() => setFilter('approved')}
                >
                    Approved
                </button>
                <button
                    className={`filter-btn ${filter === 'rejected' ? 'active' : ''}`}
                    onClick={() => setFilter('rejected')}
                >
                    Rejected
                </button>
            </div>

            <div className="change-requests-list">
                {loading ? (
                    <div className="loading-state">Loading change requests...</div>
                ) : requests.length === 0 ? (
                    <div className="empty-state">
                        <p>No change requests found</p>
                        <button className="btn-primary" onClick={() => navigate('/change-requests/new')}>
                            Create your first request
                        </button>
                    </div>
                ) : (
                    <div className="requests-grid">
                        {requests.map(request => (
                            <div key={request.id} className="request-card">
                                <div className="request-header">
                                    <span className="request-type">{getRequestTypeLabel(request.request_type)}</span>
                                    <span className={`badge ${getStatusBadge(request.status).class}`}>
                                        {getStatusBadge(request.status).text}
                                    </span>
                                </div>

                                <div className="request-body">
                                    <div className="request-field">
                                        <label>Current Value:</label>
                                        <span>{request.current_value}</span>
                                    </div>
                                    <div className="request-field">
                                        <label>Requested Value:</label>
                                        <span className="highlight">{request.requested_value}</span>
                                    </div>
                                    <div className="request-field">
                                        <label>Reason:</label>
                                        <p>{request.reason}</p>
                                    </div>
                                </div>

                                <div className="request-footer">
                                    <span className="request-date">
                                        {new Date(request.created_at).toLocaleDateString()}
                                    </span>
                                    {request.status === 'pending' && (
                                        <button className="btn-link">View Details</button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default ChangeRequests;
