// 聊天消息存储
let messages = []

exports.handler = async (event) => {
  // 处理跨域
  const headers = {
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET,POST,OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type'
  }

  // 预检请求
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers }
  }

  // 发消息
  if (event.httpMethod === 'POST') {
    try {
      const data = JSON.parse(event.body || '{}')
      messages.push({
        name: data.name || '游客',
        text: data.text || '',
        time: new Date().toLocaleTimeString() // 发送时间
      })
      // 限制最多100条
      if (messages.length > 100) messages.shift()
      return { statusCode: 200, headers, body: JSON.stringify({ code: 0 }) }
    } catch (e) {
      return { statusCode: 400, headers, body: JSON.stringify({ error: '参数错误' }) }
    }
  }

  // 获取消息列表
  return {
    statusCode: 200,
    headers,
    body: JSON.stringify(messages)
  }
}
