import { useAuth } from '../../hooks/useAuth';
import { Navbar } from '../../components/layout';
import { Card } from '../../components/common';
import {
  BuildingOfficeIcon,
  HomeModernIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
} from '@heroicons/react/24/outline';

const Dashboard = () => {
  const { user } = useAuth();

  const stats = [
    {
      name: 'Total Properties',
      value: '0',
      icon: BuildingOfficeIcon,
      color: 'bg-blue-500',
    },
    {
      name: 'Total Units',
      value: '0',
      icon: HomeModernIcon,
      color: 'bg-green-500',
    },
    {
      name: 'Active Tenants',
      value: '0',
      icon: UserGroupIcon,
      color: 'bg-purple-500',
    },
    {
      name: 'Monthly Revenue',
      value: 'KES 0',
      icon: CurrencyDollarIcon,
      color: 'bg-amber-500',
    },
  ];

  return (
    <div className="min-h-screen bg-bg">
      <Navbar />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Welcome Section */}
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-text">
            Welcome back, {user?.name?.split(' ')[0] || 'User'}!
          </h1>
          <p className="text-muted mt-1">
            Here's what's happening with your properties today.
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {stats.map((stat) => (
            <Card key={stat.name} padding="md">
              <div className="flex items-center gap-4">
                <div className={`${stat.color} p-3 rounded-lg`}>
                  <stat.icon className="h-6 w-6 text-white" />
                </div>
                <div>
                  <p className="text-sm text-muted">{stat.name}</p>
                  <p className="text-2xl font-bold text-text">{stat.value}</p>
                </div>
              </div>
            </Card>
          ))}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card padding="md">
            <h2 className="text-lg font-semibold text-text mb-4">Quick Actions</h2>
            <div className="space-y-3">
              <button className="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                <p className="font-medium text-text">Add New Property</p>
                <p className="text-sm text-muted">Register a new property to manage</p>
              </button>
              <button className="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                <p className="font-medium text-text">Add New Unit</p>
                <p className="text-sm text-muted">Add a unit to an existing property</p>
              </button>
              <button className="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                <p className="font-medium text-text">Record Payment</p>
                <p className="text-sm text-muted">Record a tenant payment</p>
              </button>
            </div>
          </Card>

          <Card padding="md">
            <h2 className="text-lg font-semibold text-text mb-4">Recent Activity</h2>
            <div className="text-center py-8">
              <p className="text-muted">No recent activity</p>
              <p className="text-sm text-muted mt-1">
                Your recent actions will appear here
              </p>
            </div>
          </Card>
        </div>
      </main>
    </div>
  );
};

export default Dashboard;
