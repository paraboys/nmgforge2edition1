import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import api from '../api/axios';
import { useAuth } from '../contexts/AuthContext';

export default function TicketDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const [ticket, setTicket] = useState(null);
  const [conversations, setConversations] = useState([]);
  const [replyBody, setReplyBody] = useState('');
  const [isInternal, setIsInternal] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchTicket();
  }, [id]);

  const fetchTicket = async () => {
    try {
      const [ticketRes, convRes] = await Promise.all([
        api.get(`/tickets/${id}`),
        api.get(`/tickets/${id}/comments`)
      ]);
      setTicket(ticketRes.data.data || ticketRes.data);
      setConversations(convRes.data.data || convRes.data);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const handleReply = async (e) => {
    e.preventDefault();
    if (!replyBody.trim()) return;

    try {
      const response = await api.post(`/tickets/${id}/comments`, {
        body: replyBody,
        is_internal: isInternal
      });
      setConversations([response.data.data || response.data, ...conversations]);
      setReplyBody('');
    } catch (error) {
      console.error(error);
    }
  };

  const handleStatusChange = async (newStatus) => {
    try {
      await api.put(`/tickets/${id}`, { status: newStatus });
      setTicket({ ...ticket, status: newStatus });
    } catch (error) {
      console.error(error);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (!ticket) return <div>Ticket not found.</div>;

  return (
    <div className="max-w-4xl mx-auto">
      <div className="bg-white shadow sm:rounded-lg mb-6">
        <div className="px-4 py-5 sm:px-6 flex justify-between items-start">
          <div>
            <h3 className="text-lg leading-6 font-medium text-gray-900">
              {ticket.subject}
            </h3>
            <p className="mt-1 max-w-2xl text-sm text-gray-500">
              Reported by {ticket.requester.name} on {new Date(ticket.created_at).toLocaleString()}
            </p>
          </div>
          <div className="flex space-x-2 text-sm uppercase items-center">
            {user.role !== 'customer' ? (
              <select
                className="bg-gray-100 text-gray-800 px-2 py-1 rounded font-medium border-0 focus:ring-primary"
                value={ticket.status}
                onChange={(e) => handleStatusChange(e.target.value)}
              >
                <option value="open">OPEN</option>
                <option value="pending">PENDING</option>
                <option value="resolved">RESOLVED</option>
                <option value="closed">CLOSED</option>
              </select>
            ) : (
              <span className="bg-gray-100 text-gray-800 px-2 py-1 rounded font-medium">{ticket.status}</span>
            )}
            <span className="bg-gray-100 text-gray-800 px-2 py-1 rounded font-medium">{ticket.priority}</span>
          </div>
        </div>
        <div className="border-t border-gray-200 px-4 py-5 sm:px-6">
          <p className="text-gray-700 whitespace-pre-wrap">{ticket.description}</p>
        </div>
      </div>

      <div className="space-y-6">
        <div className="bg-white shadow sm:rounded-lg p-4">
          <form onSubmit={handleReply}>
            <textarea
              rows={4}
              className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary p-2 border"
              placeholder="Write a reply..."
              value={replyBody}
              onChange={(e) => setReplyBody(e.target.value)}
            />
            <div className="mt-3 flex items-center justify-between">
              {user.role !== 'customer' ? (
                <label className="flex items-center text-sm text-gray-600">
                  <input
                    type="checkbox"
                    className="mr-2"
                    checked={isInternal}
                    onChange={(e) => setIsInternal(e.target.checked)}
                  />
                  Internal Note
                </label>
              ) : <div></div>}
              <button
                type="submit"
                className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none"
              >
                Send Reply
              </button>
            </div>
          </form>
        </div>

        {conversations.map((conv) => (
          <div key={conv.id} className={`bg-white shadow sm:rounded-lg p-4 ${conv.is_internal ? 'border-l-4 border-yellow-400 bg-yellow-50' : ''}`}>
            <div className="flex justify-between items-center mb-2 text-sm text-gray-500">
              <span className="font-medium text-gray-900">{conv.user.name}</span>
              <span>{new Date(conv.created_at).toLocaleString()}</span>
            </div>
            {conv.is_internal && <span className="text-xs font-bold text-yellow-600 uppercase mb-2 block">Internal Note</span>}
            <p className="text-gray-700 whitespace-pre-wrap">{conv.body}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
