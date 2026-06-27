import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../contexts/AuthContext';

export default function Settings() {
  const { user } = useAuth();
  const [users, setUsers] = useState([]);
  const [slaPolicies, setSlaPolicies] = useState([]);
  const [loading, setLoading] = useState(true);

  // New user form state
  const [newUserName, setNewUserName] = useState('');
  const [newUserEmail, setNewUserEmail] = useState('');
  const [newUserPassword, setNewUserPassword] = useState('');
  const [newUserRole, setNewUserRole] = useState('agent');
  const [creatingUser, setCreatingUser] = useState(false);
  const [userError, setUserError] = useState('');

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

  const handleCreateUser = async (e) => {
    e.preventDefault();
    setCreatingUser(true);
    setUserError('');
    try {
      const response = await api.post('/users', {
        name: newUserName,
        email: newUserEmail,
        password: newUserPassword,
        role: newUserRole
      });
      const newUser = response.data.data || response.data;
      setUsers([newUser, ...users]);
      setNewUserName('');
      setNewUserEmail('');
      setNewUserPassword('');
    } catch (err) {
      setUserError('Failed to create user.');
    } finally {
      setCreatingUser(false);
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
          <h3 className="text-lg leading-6 font-medium text-gray-900">Create User</h3>
          <p className="mt-1 max-w-2xl text-sm text-gray-500">Add a new agent or customer to your organization.</p>
        </div>
        <div className="border-t border-gray-200 px-4 py-5 sm:px-6">
          <form onSubmit={handleCreateUser} className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            {userError && <div className="sm:col-span-6 text-sm text-red-600">{userError}</div>}
            <div className="sm:col-span-2">
              <label className="block text-sm font-medium text-gray-700">Name</label>
              <input type="text" required value={newUserName} onChange={e => setNewUserName(e.target.value)} className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" />
            </div>
            <div className="sm:col-span-2">
              <label className="block text-sm font-medium text-gray-700">Email</label>
              <input type="email" required value={newUserEmail} onChange={e => setNewUserEmail(e.target.value)} className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" />
            </div>
            <div className="sm:col-span-1">
              <label className="block text-sm font-medium text-gray-700">Password</label>
              <input type="password" required minLength={8} value={newUserPassword} onChange={e => setNewUserPassword(e.target.value)} className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" />
            </div>
            <div className="sm:col-span-1">
              <label className="block text-sm font-medium text-gray-700">Role</label>
              <select value={newUserRole} onChange={e => setNewUserRole(e.target.value)} className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                <option value="agent">Agent</option>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div className="sm:col-span-6">
              <button type="submit" disabled={creatingUser} className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none disabled:opacity-50">
                {creatingUser ? 'Creating...' : 'Create User'}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">Users Directory</h3>
        </div>
        <div className="border-t border-gray-200">
          <ul className="divide-y divide-gray-200 max-h-96 overflow-y-auto">
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
            {users.length === 0 && <li className="px-4 py-4 text-gray-500">No users found.</li>}
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
                <button className="text-primary hover:text-primary-hover text-sm font-medium cursor-not-allowed opacity-50">
                  Edit (Coming Soon)
                </button>
              </li>
            ))}
            {slaPolicies.length === 0 && <li className="px-4 py-4 text-gray-500">No SLA policies found.</li>}
          </ul>
        </div>
      </div>
    </div>
  );
}
