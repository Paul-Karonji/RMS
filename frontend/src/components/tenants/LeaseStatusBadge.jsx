import React from 'react';

/**
 * LeaseStatusBadge - Display lease status with color coding
 * @param {string} status - 'active', 'terminated', 'expired', 'pending'
 * @param {string} endDate - Lease end date for expiring soon calculation
 */
const LeaseStatusBadge = ({ status, endDate }) => {
    const isExpiringSoon = () => {
        if (!endDate || status !== 'active') return false;
        const end = new Date(endDate);
        const now = new Date();
        const daysRemaining = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
        return daysRemaining <= 60 && daysRemaining > 0;
    };

    const getStatusStyles = () => {
        // Check if expiring soon (within 60 days)
        if (isExpiringSoon()) {
            return {
                bg: 'bg-orange-100',
                text: 'text-orange-800',
                dot: 'bg-orange-500',
                label: 'Expiring Soon',
            };
        }

        switch (status?.toLowerCase()) {
            case 'active':
                return {
                    bg: 'bg-green-100',
                    text: 'text-green-800',
                    dot: 'bg-green-500',
                    label: 'Active',
                };
            case 'terminated':
                return {
                    bg: 'bg-red-100',
                    text: 'text-red-800',
                    dot: 'bg-red-500',
                    label: 'Terminated',
                };
            case 'expired':
                return {
                    bg: 'bg-gray-100',
                    text: 'text-gray-600',
                    dot: 'bg-gray-400',
                    label: 'Expired',
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

export default LeaseStatusBadge;
