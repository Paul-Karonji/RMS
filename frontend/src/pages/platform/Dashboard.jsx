import React, { useState, useEffect } from 'react';
import { platformService } from '../../services/platform';
import MetricsCard from '../../components/platform/MetricsCard';
import { toast } from 'react-toastify';

const Dashboard = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchDashboard();
  }, []);
  
  const fetchDashboard = async () => {
    try {
      const response = await platformService.getDashboard();
      setData(response.data.data);
    } catch (error) {
      console.error('Failed to fetch dashboard:', error);
      toast.error('Failed to load dashboard data');
    } finally {
      setLoading(false);
    }
  };
  
  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Loading dashboard...</div>
      </div>
    );
  }

  if (!data) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">No data available</div>
      </div>
    );
  }
  
  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">Platform Dashboard</h1>
        <div className="text-sm text-gray-500">
          {data.period?.start_date} to {data.period?.end_date}
        </div>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricsCard
          title="Total Companies"
          value={data.metrics?.total_companies || 0}
          change={data.metrics?.new_companies_this_month || 0}
          changeLabel="new this month"
          trend="up"
        />
        <MetricsCard
          title="Total Revenue"
          value={`KES ${(data.metrics?.total_revenue || 0).toLocaleString()}`}
        />
        <MetricsCard
          title="Cashout Fees"
          value={`KES ${(data.metrics?.revenue_from_cashouts || 0).toLocaleString()}`}
        />
        <MetricsCard
          title="Subscriptions"
          value={`KES ${(data.metrics?.revenue_from_subscriptions || 0).toLocaleString()}`}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold mb-4">Companies by Model</h3>
          <div className="space-y-3">
            {data.companies_by_model && Object.entries(data.companies_by_model).map(([model, count]) => (
              <div key={model} className="flex items-center justify-between">
                <span className="text-gray-600 capitalize">
                  {model === 'payment_processing' ? 'Payment Processing' : 'Listings Only'}
                </span>
                <span className="font-semibold">{count}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold mb-4">Recent Companies</h3>
          <div className="space-y-3">
            {data.recent_companies?.map((company) => (
              <div key={company.id} className="flex items-center justify-between py-2 border-b last:border-b-0">
                <div>
                  <p className="font-medium text-gray-900">{company.company_name}</p>
                  <p className="text-sm text-gray-500">{company.admin_name}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded ${
                  company.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                }`}>
                  {company.status}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-semibold mb-4">Top Performing Companies</h3>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Earned</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashed Out</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {data.top_companies?.map((company, index) => (
                <tr key={index}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {company.company_name}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    KES {company.total_earned?.toLocaleString() || 0}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    KES {company.total_cashed_out?.toLocaleString() || 0}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    KES {company.available_balance?.toLocaleString() || 0}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
