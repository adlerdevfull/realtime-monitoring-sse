#!/bin/bash
# Demo: Painel de Monitoramento em Tempo Real com SSE
# Uso: ./scripts/demo.sh

BASE_URL="http://localhost:8000/api"
echo "=== realtime-monitoring-sse ==="
echo ""

echo "1. Health Check"
curl -s $BASE_URL/v1/health | jq .
echo ""

echo "2. Login (admin@platform.test / password)"
TOKEN=$(curl -s -X POST $BASE_URL/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@platform.test","password":"password"}' | jq -r '.token // .access_token')
echo "   Token: ${TOKEN:0:30}..."
echo ""

echo "3. Dispatch Event"
curl -s -X POST $BASE_URL/v1/events/dispatch \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"alert","payload":{"level":"warning","message":"CPU usage at 85%","server":"web-01"}}' | jq .
echo ""

echo "4. Dispatch Another Event"
curl -s -X POST $BASE_URL/v1/events/dispatch \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"metric","payload":{"name":"response_time","value":245,"unit":"ms"}}' | jq .
echo ""

echo "5. Recent Events"
curl -s $BASE_URL/v1/events/recent \
  -H "Authorization: Bearer $TOKEN" | jq '.data'
echo ""

echo "6. Active Connections"
curl -s $BASE_URL/v1/events/connections \
  -H "Authorization: Bearer $TOKEN" | jq .
echo ""

echo "7. SSE Stream (5 seconds sample)"
echo "   Conectando al stream SSE..."
timeout 5 curl -s -N $BASE_URL/v1/events \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: text/event-stream" 2>/dev/null || true
echo ""
echo "   (Stream cerrado después de 5s)"
echo ""

echo "=== Demo completada ==="
echo ""
echo "TIP: Para ver SSE en tiempo real, abre 2 terminales:"
echo "  Terminal 1: curl -N $BASE_URL/v1/events -H 'Authorization: Bearer TOKEN' -H 'Accept: text/event-stream'"
echo "  Terminal 2: curl -X POST $BASE_URL/v1/events/dispatch -H 'Authorization: Bearer TOKEN' -H 'Content-Type: application/json' -d '{\"type\":\"test\",\"payload\":{\"msg\":\"hello\"}}'"
