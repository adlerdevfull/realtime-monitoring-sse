import { useState, useEffect } from 'react'
import { events } from '../services/api'
import { Radio, Users, Activity } from 'lucide-react'
export default function Dashboard() {
  const [recent, setRecent] = useState([])
  const [conns, setConns] = useState(0)
  useEffect(() => {
    events.recent().then(r => setRecent(r.data.data || r.data || [])).catch(() => {})
    events.connections().then(r => setConns(r.data.connections || r.data.count || 0)).catch(() => {})
  }, [])
  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Dashboard</h2>
      <div className="grid grid-cols-3 gap-4 mb-8">
        <div className="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4"><div className="bg-rose-500 p-3 rounded-lg text-white"><Radio size={24} /></div><div><p className="text-sm text-gray-500">Eventos Recientes</p><p className="text-2xl font-bold">{recent.length}</p></div></div>
        <div className="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4"><div className="bg-green-500 p-3 rounded-lg text-white"><Users size={24} /></div><div><p className="text-sm text-gray-500">Conexiones SSE</p><p className="text-2xl font-bold">{conns}</p></div></div>
        <div className="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4"><div className="bg-blue-500 p-3 rounded-lg text-white"><Activity size={24} /></div><div><p className="text-sm text-gray-500">Protocolo</p><p className="text-2xl font-bold">SSE</p></div></div>
      </div>
      <div className="bg-white rounded-xl shadow-sm p-6">
        <h3 className="font-semibold mb-3">Features SSE</h3>
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div className="p-3 bg-gray-50 rounded-lg"><p className="font-medium">Reconnection</p><p className="text-gray-400 text-xs">Last-Event-ID para no perder eventos</p></div>
          <div className="p-3 bg-gray-50 rounded-lg"><p className="font-medium">Heartbeat</p><p className="text-gray-400 text-xs">Ping cada 30s para mantener conexión</p></div>
          <div className="p-3 bg-gray-50 rounded-lg"><p className="font-medium">Redis Persistence</p><p className="text-gray-400 text-xs">Eventos persistidos en Redis Streams</p></div>
          <div className="p-3 bg-gray-50 rounded-lg"><p className="font-medium">Memory Management</p><p className="text-gray-400 text-xs">Límite de conexiones + GC</p></div>
        </div>
      </div>
    </div>
  )
}
