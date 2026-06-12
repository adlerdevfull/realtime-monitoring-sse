import { useState } from 'react'
import { events } from '../services/api'
import { Send, Zap, AlertTriangle, BarChart3 } from 'lucide-react'

const presets = [
  { type: 'alert', payload: { level: 'warning', message: 'CPU usage at 85%', server: 'web-01' }, icon: AlertTriangle, color: 'bg-yellow-50 border-yellow-200' },
  { type: 'metric', payload: { name: 'response_time', value: 245, unit: 'ms' }, icon: BarChart3, color: 'bg-blue-50 border-blue-200' },
  { type: 'deploy', payload: { service: 'api', version: '2.1.0', status: 'success' }, icon: Zap, color: 'bg-green-50 border-green-200' },
  { type: 'alert', payload: { level: 'critical', message: 'Database connection pool exhausted', server: 'db-01' }, icon: AlertTriangle, color: 'bg-red-50 border-red-200' },
]

export default function Dispatch() {
  const [custom, setCustom] = useState('{"type":"test","payload":{"message":"Hello SSE!"}}')
  const [sent, setSent] = useState([])

  const send = async (type, payload) => {
    await events.dispatch({ type, payload })
    setSent(prev => [...prev, { type, payload, time: new Date().toLocaleTimeString() }])
  }

  const sendCustom = async () => {
    try {
      const parsed = JSON.parse(custom)
      await events.dispatch(parsed)
      setSent(prev => [...prev, { ...parsed, time: new Date().toLocaleTimeString() }])
    } catch {}
  }

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Dispatch Events</h2>

      <div className="grid grid-cols-2 gap-3 mb-6">
        {presets.map((p, i) => (
          <button key={i} onClick={() => send(p.type, p.payload)} className={`p-4 border rounded-xl text-left hover:shadow-md transition ${p.color}`}>
            <div className="flex items-center gap-2 mb-2">
              <p.icon size={16} />
              <span className="font-medium text-sm">{p.type}</span>
            </div>
            <pre className="text-xs text-gray-600">{JSON.stringify(p.payload, null, 1)}</pre>
          </button>
        ))}
      </div>

      <div className="bg-white rounded-xl shadow-sm p-5 mb-6">
        <h3 className="font-semibold mb-3">Evento Custom</h3>
        <textarea value={custom} onChange={e => setCustom(e.target.value)} className="w-full h-24 px-3 py-2 border rounded-lg font-mono text-sm" />
        <button onClick={sendCustom} className="mt-2 bg-rose-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-rose-700 flex items-center gap-2"><Send size={16} /> Enviar</button>
      </div>

      {sent.length > 0 && (
        <div className="bg-gray-50 rounded-xl p-4">
          <h3 className="font-semibold mb-2 text-sm">Enviados ({sent.length})</h3>
          {sent.slice(-5).reverse().map((s, i) => (
            <p key={i} className="text-xs text-gray-500">[{s.time}] {s.type}: {JSON.stringify(s.payload)}</p>
          ))}
        </div>
      )}
    </div>
  )
}
