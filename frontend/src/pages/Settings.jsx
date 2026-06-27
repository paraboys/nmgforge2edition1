import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../contexts/AuthContext';

export default function Settings() {
  const { user } = useAuth();
  const [users, setUsers] = useState([]);
  const [slaPolicies, setSlaPolicies] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user.role !== 'admin') return;
    fetchData();
  }, [user]);

  const fetchData = async () => {
    try {
      const [usersRes, slaRes] = await Promise.all([
        api.get('/users'),
        api.get('/sla-policies')
      ]);
      setUsers(usersRes.data.data || usersRes.data);
      setSlaPolicies(slaRes.data.data || slaRes.data);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  if (user.role !== 'admin') {
    return <div>You do not have permission to view this page.</div>;
  }

  if (loading) return <div>Loading...</div>;

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Settings</h1>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">Users</h3>
        </div>
        <div className="border-t border-gray-200">
          <ul className="divide-y divide-gray-200">
            {users.map((u) => (
              <li key={u.id} className="px-4 py-4 sm:px-6 flex justify-between items-center">
                <div>
                  <p className="text-sm font-medium text-gray-900">{u.name}</p>
                  <p className="text-sm text-gray-500">{u.email}</p>
                </div>
                <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 uppercase">
                  {u.role}
                </span>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">SLA Policies</h3>
        </div>
        <div className="border-t border-gray-200">
          <ul className="divide-y divide-gray-200">
            {slaPolicies.map((sla) => (
              <li key={sla.id} className="px-4 py-4 sm:px-6 flex justify-between items-center">
                <div>
                  <p className="text-sm font-medium text-gray-900 capitalize">{sla.priority} Priority</p>
                  <p className="text-sm text-gray-500">
                    Response: {sla.response_time_minutes}m | Resolution: {sla.resolution_time_minutes}m
                  </p>
                </div>
                <button className="text-primary hover:text-primary-hover text-sm font-medium">
                  Edit
                </button>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}
