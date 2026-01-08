import React from 'react';
import { Link } from 'react-router-dom';
import TenantStatusBadge from './TenantStatusBadge';

/**
 * TenantCard - Compact card for tenant list display
 * @param {Object} tenant - Tenant data object
 * @param {Function} onDelete - Delete handler
 */
const TenantCard = ({ tenant, onDelete }) => {
    const getInitials = (name) => {
        if (!name) return '?';
        return name
            .split(' ')
            .map((word) => word[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const getAvatarColor = (name) => {
        const colors = [
            'bg-blue-500',
            'bg-green-500',
            'bg-purple-500',
            'bg-pink-500',
            'bg-indigo-500',
            'bg-teal-500',
        ];
        const index = name ? name.charCodeAt(0) % colors.length : 0;
        return colors[index];
    };

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 overflow-hidden">
            <div className="p-5">
                <div className="flex items-start gap-4">
                    {/* Avatar */}
                    <div
                        className={`w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm ${getAvatarColor(
                            tenant.name
                        )}`}
                    >
                        {getInitials(tenant.name)}
                    </div>

                    {/* Info */}
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between gap-2">
                            <Link
                                to={`/company/tenants/${tenant.id}`}
                                className="font-semibold text-gray-900 hover:text-blue-600 truncate"
                            >
                                {tenant.name}
                            </Link>
                            <TenantStatusBadge status={tenant.is_active ? 'active' : 'inactive'} />
                        </div>

                        <p className="text-sm text-gray-500 truncate mt-0.5">{tenant.email}</p>

                        <div className="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            {/* Phone */}
                            <div className="flex items-center gap-1">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                    />
                                </svg>
                                <span>{tenant.phone}</span>
                            </div>

                            {/* Current Unit */}
                            {tenant.current_lease?.unit && (
                                <div className="flex items-center gap-1">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        />
                                    </svg>
                                    <span>Unit {tenant.current_lease.unit.unit_number}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                    <Link
                        to={`/company/tenants/${tenant.id}`}
                        className="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        View Details
                    </Link>
                    <Link
                        to={`/company/tenants/${tenant.id}/edit`}
                        className="flex-1 text-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                    >
                        Edit
                    </Link>
                    {onDelete && (
                        <button
                            onClick={() => onDelete(tenant)}
                            className="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default TenantCard;
