const PropertyStatusBadge = ({ status }) => {
  const statusConfig = {
    pending_approval: {
      label: 'Pending Approval',
      className: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    },
    approved: {
      label: 'Approved',
      className: 'bg-green-100 text-green-800 border-green-200',
    },
    rejected: {
      label: 'Rejected',
      className: 'bg-red-100 text-red-800 border-red-200',
    },
    suspended: {
      label: 'Suspended',
      className: 'bg-gray-100 text-gray-800 border-gray-200',
    },
    deleted: {
      label: 'Deleted',
      className: 'bg-gray-100 text-gray-600 border-gray-200',
    },
  };

  const config = statusConfig[status] || {
    label: status || 'Unknown',
    className: 'bg-gray-100 text-gray-800 border-gray-200',
  };

  return (
    <span
      className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${config.className}`}
    >
      {config.label}
    </span>
  );
};

export default PropertyStatusBadge;
