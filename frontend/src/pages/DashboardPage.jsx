import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../contexts/AuthContext';
import { Ticket, Users, CheckCircle } from 'lucide-react';

export default function Dashboard() {
  const { user } = useAuth();
  const [metrics, setMetrics] = useState({ open_tickets: 0, unassigned: 0, resolved_today: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchMetrics = async () => {
      try {
        const response = await api.get('/dashboard/metrics');
        setMetrics(response.data);
      } catch (error) {
        console.error('Failed to fetch metrics', error);
      } finally {
        setLoading(false);
      }
    };
    fetchMetrics();
  }, []);

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
      
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <Ticket className="h-6 w-6 text-gray-400" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">
                    Open Tickets
                  </dt>
                  <dd className="text-3xl font-semibold text-gray-900">
                    {metrics.open_tickets}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        {user.role !== 'customer' && (
          <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <Users className="h-6 w-6 text-gray-400" />
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      Unassigned
                    </dt>
                    <dd className="text-3xl font-semibold text-gray-900">
                      {metrics.unassigned}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        )}

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <CheckCircle className="h-6 w-6 text-gray-400" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">
                    Resolved Today
                  </dt>
                  <dd className="text-3xl font-semibold text-gray-900">
                    {metrics.resolved_today}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
