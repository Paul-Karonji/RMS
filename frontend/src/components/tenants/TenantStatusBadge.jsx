import React from 'react';

/**
 * TenantStatusBadge - Display tenant status with color coding
 * @param {string} status - 'active', 'inactive', 'pending'
 */
const TenantStatusBadge = ({ status }) => {
    const getStatusStyles = () => {
        switch (status?.toLowerCase()) {
            case 'active':
                return {
                    bg: 'bg-green-100',
                    text: 'text-green-800',
                    dot: 'bg-green-500',
                    label: 'Active',
                };
            case 'inactive':
                return {
                    bg: 'bg-gray-100',
                    text: 'text-gray-600',
                    dot: 'bg-gray-400',
                    label: 'Inactive',
                };
            case 'pending':
                return {
                    bg: 'bg-yellow-100',
                    text: 'text-yellow-800',
                    dot: 'bg-yellow-500',
                    label: 'Pending',
                };
            default:
                return {
                    bg: 'bg-gray-100',
                    text: 'text-gray-600',
                    dot: 'bg-gray-400',
                    label: status || 'Unknown',
                };
        }
    };

    const styles = getStatusStyles();

    return (
        <span
            className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${styles.bg} ${styles.text}`}
        >
            <span className={`w-1.5 h-1.5 rounded-full ${styles.dot}`}></span>
            {styles.label}
        </span>
    );
};

export default TenantStatusBadge;
