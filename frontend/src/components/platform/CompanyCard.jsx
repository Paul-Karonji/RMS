import React from 'react';
import { Link } from 'react-router-dom';

const CompanyCard = ({ company, onSuspend, onActivate }) => {
  const getStatusColor = (status) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'suspended': return 'bg-red-100 text-red-800';
      case 'deleted': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPricingModelBadge = (model) => {
    return model === 'payment_processing' 
      ? 'bg-blue-100 text-blue-800' 
      : 'bg-purple-100 text-purple-800';
  };

  return (
    <div className="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <Link 
            to={`/platform/companies/${company.id}`}
            className="text-lg font-semibold text-gray-900 hover:text-blue-600"
          >
            {company.company_name}
          </Link>
          <p className="mt-1 text-sm text-gray-500">{company.company_email}</p>
          
          <div className="mt-3 flex items-center space-x-2">
            <span className={`px-2 py-1 text-xs font-medium rounded ${getStatusColor(company.status)}`}>
              {company.status}
            </span>
            <span className={`px-2 py-1 text-xs font-medium rounded ${getPricingModelBadge(company.pricing_model)}`}>
              {company.pricing_model === 'payment_processing' ? 'Payment Processing' : 'Listings Only'}
            </span>
          </div>

          {company.balance && (
            <div className="mt-4 grid grid-cols-2 gap-4 text-sm">
              <div>
                <p className="text-gray-500">Available Balance</p>
                <p className="font-semibold">KES {company.balance.available_balance?.toLocaleString() || 0}</p>
              </div>
              <div>
                <p className="text-gray-500">Total Earned</p>
                <p className="font-semibold">KES {company.balance.total_earned?.toLocaleString() || 0}</p>
              </div>
            </div>
          )}
        </div>

        <div className="flex-shrink-0 ml-4">
          <div className="flex space-x-2">
            {company.status === 'active' && (
              <button
                onClick={() => onSuspend(company.id)}
                className="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded"
              >
                Suspend
              </button>
            )}
            {company.status === 'suspended' && (
              <button
                onClick={() => onActivate(company.id)}
                className="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded"
              >
                Activate
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default CompanyCard;
