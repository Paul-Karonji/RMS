const UnitStatusBadge = ({ status }) => {
  const statusConfig = {
    vacant: {
      label: 'Vacant',
      className: 'bg-green-100 text-green-800 border-green-200',
    },
    occupied: {
      label: 'Occupied',
      className: 'bg-blue-100 text-blue-800 border-blue-200',
    },
    reserved: {
      label: 'Reserved',
      className: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    },
    under_maintenance: {
      label: 'Maintenance',
      className: 'bg-orange-100 text-orange-800 border-orange-200',
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

export default UnitStatusBadge;
