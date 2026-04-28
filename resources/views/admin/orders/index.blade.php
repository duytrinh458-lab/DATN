<div style="
    max-width: 1000px;
    margin: 30px auto;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    color: #e0f7fa;
    font-family: 'Segoe UI', Tahoma, sans-serif;
">

    <h2 style="color:#00e5ff; margin-bottom:20px;">
        Quản lý đơn hàng
    </h2>

    @if(session('success'))
        <div style="color:#00e676; margin-bottom:15px;">
            {{ session('success') }}
        </div>
    @endif

    <table style="
        width:100%;
        border-collapse: collapse;
        overflow: hidden;
        border-radius: 10px;
    ">

        <tr style="background: rgba(0,188,212,0.3);">
            <th style="padding:10px; text-align:left;">ID</th>
            <th style="padding:10px; text-align:left;">Khách hàng</th>
            <th style="padding:10px; text-align:left;">SĐT</th>
            <th style="padding:10px; text-align:left;">Tổng tiền</th>
            <th style="padding:10px; text-align:left;">Trạng thái</th>
            <th style="padding:10px; text-align:left;">Ngày</th>
            <th style="padding:10px; text-align:left;">Action</th>
        </tr>

        @foreach($orders as $order)
        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">

            <td style="padding:10px;">#{{ $order->id }}</td>

            <td style="padding:10px;">
                {{ $order->full_name }}
            </td>

            <td style="padding:10px;">
                {{ $order->phone }}
            </td>

            <td style="padding:10px; color:#00e5ff; font-weight:bold;">
                {{ number_format($order->total) }}đ
            </td>

            <td style="padding:10px;">
                <span style="
                    padding:4px 8px;
                    border-radius:6px;
                    font-size:12px;
                    color:white;
                    background:
                    @if($order->status == 'pending') orange
                    @elseif($order->status == 'confirmed') #17a2b8
                    @elseif($order->status == 'shipped') #007bff
                    @elseif($order->status == 'completed') #28a745
                    @else red
                    @endif
                ">
                    {{ $order->status }}
                </span>
            </td>

            <td style="padding:10px;">
                {{ $order->created_at }}
            </td>

            <td style="padding:10px;">
                <a href="{{ route('admin.orders.show', $order->id) }}"
                   style="
                        background:#00bcd4;
                        color:white;
                        padding:6px 12px;
                        border-radius:6px;
                        text-decoration:none;
                        transition:0.3s;
                   "
                   onmouseover="this.style.background='#00e5ff'"
                   onmouseout="this.style.background='#00bcd4'"
                >
                    Xem
                </a>
            </td>

        </tr>
        @endforeach
    </table>
</div>