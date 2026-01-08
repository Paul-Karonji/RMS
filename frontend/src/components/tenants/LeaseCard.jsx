import React from 'react';
import { Link } from 'react-router-dom';
import LeaseStatusBadge from './LeaseStatusBadge';
import tenantService from '../../services/tenantService';

/**
 * LeaseCard - Lease summary card for list display
 * @param {Object} lease - Lease data object
 * @param {Function} onTerminate - Terminate handler
 * @param {Function} onRenew - Renew handler
 */
const LeaseCard = ({ lease, onTerminate, onRenew }) => {
    const getDaysRemaining = () => {
        if (!lease.end_date || lease.status !== 'active') return null;
        const end = new Date(lease.end_date);
        const now = new Date();
        return Math.ceil((end - now) / (1000 * 60 * 60 * 24));
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const daysRemaining = getDaysRemaining();

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 overflow-hidden">
            <div className="p-5">
                {/* Header */}
                <div className="flex items-start justify-between gap-3 mb-4">
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-1">
                            <span className="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                {lease.lease_number}
                            </span>
                            <LeaseStatusBadge status={lease.status} endDate={lease.end_date} />
                        </div>
                        <h3 className="font-semibold text-gray-900 truncate">
                            {lease.tenant?.name || 'Unknown Tenant'}
                        </h3>
                    </div>
                </div>

                {/* Unit & Property */}
                <div className="flex items-center gap-2 text-sm text-gray-600 mb-3">
                    <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <span>
                        Unit {lease.unit?.unit_number} • {lease.property?.property_name}
                    </span>
                </div>

                {/* Dates */}
                <div className="grid grid-cols-2 gap-3 mb-3">
                    <div className="bg-gray-50 rounded-lg px-3 py-2">
                        <p className="text-xs text-gray-500 mb-0.5">Start Date</p>
                        <p className="text-sm font-medium text-gray-900">{formatDate(lease.start_date)}</p>
                    </div>
                    <div className="bg-gray-50 rounded-lg px-3 py-2">
                        <p className="text-xs text-gray-500 mb-0.5">End Date</p>
                        <p className="text-sm font-medium text-gray-900">{formatDate(lease.end_date)}</p>
                    </div>
                </div>

                {/* Monthly Rent */}
                <div className="flex items-center justify-between py-3 border-t border-gray-100">
                    <span className="text-sm text-gray-500">Monthly Rent</span>
                    <span className="text-lg font-bold text-gray-900">
                        {tenantService.formatCurrency(lease.monthly_rent)}
                    </span>
                </div>

                {/* Days Remaining */}
                {daysRemaining !== null && lease.status === 'active' && (
                    <div
                        className={`flex items-center gap-2 px-3 py-2 rounded-lg text-sm ${daysRemaining <= 30
                                ? 'bg-red-50 text-red-700'
                                : daysRemaining <= 60
                                    ? 'bg-yellow-50 text-yellow-700'
                                    : 'bg-blue-50 text-blue-700'
                            }`}
                    >
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span className="font-medium">
                            {daysRemaining > 0 ? `${daysRemaining} days remaining` : 'Expired'}
                        </span>
                    </div>
                )}

                {/* Actions */}
                <div className="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                    <Link
                        to={`/company/leases/${lease.id}/edit`}
                        className="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        View
                    </Link>
                    {lease.status === 'active' && onRenew && (
                        <button
                            onClick={() => onRenew(lease)}
                            className="flex-1 text-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
                        >
                            Renew
                        </button>
                    )}
                    {lease.status === 'active' && onTerminate && (
                        <button
                            onClick={() => onTerminate(lease)}
                            className="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default LeaseCard;
