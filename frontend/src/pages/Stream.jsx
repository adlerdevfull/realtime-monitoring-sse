import { useState, useEffect, useRef } from 'react'
import { Radio, Wifi, WifiOff } from 'lucide-react'

export default function Stream() {
  const [events, setEvents] = useState([])
  const [connected, setConnected] = useState(false)
  const [lastId, setLastId] = useState(null)
  const eventSourceRef = useRef(null)
  const listRef = useRef(null)

  const connect = () => {
    const token = localStorage.getItem('token')
    const url = `/api/v1/events${lastId ? `?lastEventId=${lastId}` : ''}`
    const es = new EventSource(url)

    es.onopen = () => setConnected(true)
    es.onmessage = (e) => {
      try {
        const data = JSON.parse(e.data)
        setEvents(prev => [...prev.slice(-99), { id: e.lastEventId, data, time: new Date().toLocaleTimeString() }])
        if (e.lastEventId) setLastId(e.lastEventId)
      } catch {}
    }
    es.onerror = () => { setConnected(false); es.close() }
    eventSourceRef.current = es
  }

  const disconnect = () => {
    eventSourceRef.current?.close()
    setConnected(false)
  }

  useEffect(() => {
    return () => eventSourceRef.current?.close()
  }, [])

  useEffect(() => {
    listRef.current?.scrollTo(0, listRef.current.scrollHeight)
  }, [events])

  return (
    <div className="h-full flex flex-col">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-3">
          <h2 className="text-2xl font-bold">SSE Stream</h2>
          {connected ? <Wifi size={20} className="text-green-500 animate-pulse" /> : <WifiOff size={20} className="text-gray-400" />}
          <span className={`text-xs px-2 py-1 rounded-full ${connected ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
            {connected ? 'Conectado' : 'Desconectado'}
          </span>
        </div>
        <div className="flex gap-2">
          {!connected ? (
            <button onClick={connect} className="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 flex items-center gap-2"><Radio size={16} /> Conectar</button>
          ) : (
            <button onClick={disconnect} className="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Desconectar</button>
          )}
        </div>
      </div>

      <div ref={listRef} className="flex-1 bg-gray-900 rounded-xl p-4 overflow-auto font-mono text-sm">
        {events.length === 0 && (
          <p className="text-gray-500 text-center py-8">
            {connected ? 'Esperando eventos...' : 'Pulsa "Conectar" para iniciar el stream SSE'}
          </p>
        )}
        {events.map((ev, i) => (
          <div key={i} className="mb-2 border-b border-gray-800 pb-2">
            <span className="text-gray-500">[{ev.time}]</span>
            <span className="text-green-400 ml-2">id:{ev.id}</span>
            <pre className="text-gray-300 mt-1 text-xs">{JSON.stringify(ev.data, null, 2)}</pre>
          </div>
        ))}
      </div>
      <p className="text-xs text-gray-400 mt-2">Eventos recibidos: {events.length} • Last-Event-ID: {lastId || '—'}</p>
    </div>
  )
}
