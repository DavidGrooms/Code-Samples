# example: showing opening a email blast template and replacing tags with relevent code
class BlastTemplet

  def initialize(id,vid,comid)
    @@id = id
    @@vid = vid
    @@comid = comid
  end
  
  def create_templet
    id = @@id
    vid = @@vid
    comid = @@comid
    @templet = Templet.find(:first, :conditions => ["id = ?", id.to_i])
    @commit = Commitment.find(:first, :conditions => ["id = ?",comid.to_i])
    @comid = @commit.id
    t = File.open("/home/xxxx.com/backend/public/templets/#{@templet.object_name}", "r+")
    @content = t.read.to_s
    @content = @content.gsub("${comid}", @comid.to_s)
    @client = Client.find_by_id(@commit.cid)#supplier
    @content = @content.gsub("${cid}", @client.id.to_s)#supplier id
    @content = @content.gsub("${company}", @commit.company)
    
    unless @client.shipping_address.nil?
      unless @client.shipping_address2.nil? or @client.shipping_address2 == ''
        @content = @content.gsub("${street}", @client.shipping_address + " - " + @client.shipping_address2)
      else
        @content = @content.gsub("${street}", @client.shipping_address)
      end
        @content = @content.gsub("${street}", @client.shipping_address)
    else
      @content = @content.gsub("${street}", @client.mailing_address)
    end
    unless @client.shipping_city.nil?
      @content = @content.gsub("${city}", @client.shipping_city)
    else
      @content = @content.gsub("${city}", @client.mailing_city)
    end
    unless @client.shipping_state.nil?
      @content = @content.gsub("${state}", @client.shipping_state)
    else
      @content = @content.gsub("${state}", @client.mailing_state)
    end
    unless@client.shipping_zip.nil?
      @content = @content.gsub("${zip}", @client.shipping_zip)
    else
      @content = @content.gsub("${zip}", @client.mailing_zip)
    end
    unless @client.shipping_country.nil?
      @content = @content.gsub("${country}", @client.shipping_country)
    else
      @content = @content.gsub("${country}", @client.mailing_country)
    end
    if @client.phone
      @content = @content.gsub("${phone}", @client.phone)
    else
      @content = @content.gsub("${phone}", '')
    end
    if @client.fax
      @content = @content.gsub("${fax}", @client.fax)
    else
      @content = @content.gsub("${fax}", '')
    end
    if @client.web
      @content = @content.gsub("${web}", @client.web)
    else
      @content = @content.gsub("${web}", '')
    end

    @media = MediaTag.find(:all, :conditions => ['cid = ? and status = ?', @commit.cid, 'Active'])
    unless @media.nil?
      @tags = "<table><tr>"
      @media.each do |m|
        @media_icon = MediaIcon.find(:first, :conditions => ['media = ?', m.media]) 
        @tags = @tags + "<td><a href = '" + m.link + "' target='_blank'>" + "<img src='http://backend.xxxx.com/images/buttons/" + @media_icon.icon + "' alt='" + m.media + "' /></a></td>\n\r"
      end
      @tags = @tags + "</tr></table>"
      @content = @content.gsub("${media_tags}", @tags)
    else
      @content = @content.gsub("${media_tags}", '')
    end
    @content = @content.gsub("${button1}", '/images/buttons/contact_btn.jpg')
    unless @commit.enduserart == 'none'
      @content = @content.gsub("${button2}", "<img src='http://backend.xxxx.com/images/buttons/customize.gif' alt='customize and send' width='110' height='44' />")
    else
      @content = @content.gsub("${button2}", '')
    end
    @content = @content.gsub("${button3}", '/images/buttons/order_btn.jpg')
    unless @commit.supplierpdf.nil? or @commit.supplierpdf == "none" or @commit.supplierpdf == ""
      @content = @content.gsub("${digi}", @commit.supplierpdf)
      @content = @content.gsub("${button4}", "<img src='http://backend.xxxx.com/images/buttons/viewdigi.gif' alt='view catalog' width='98' height='44' />")
    else
      @content = @content.gsub("${digi}", '')
      @content = @content.gsub("${button4}", '')
    end
    unless @commit.supplierart.nil? or @commit.supplierart == '' or @commit.supplierart == 'none'
        @art_s = Upload.find(:first, :conditions => ["comid = ? and art_type = ?", @comid, 's'])
        unless @art_s.nil?
             @art = @art_s.public_filename
         else
           @art = "Art temporarily unavailable.."
        end
        @image_link = ''
        if @commit.imagemap.nil? or @commit.imagemap == ''
            unless @commit.weblink.nil? or @commit.weblink == ''
                @image_link = "<a href='http://backend.xxxx.com/templets/click/1?t=f&comid=" + @comid.to_s  + "'>"  + "<img src='http://backend.xxxx.com"  + @art  + "'></a>"
            else
                @image_link = "<img src='http://backend.xxxx.com"  + @art  + "'>"
            end
        else
            @image_link = "<img src='http://backend.xxxx.com"  + @art  + "' border='0' usemap='#Map'>" + @commit.imagemap
        end
      @content = @content.gsub("${image_link}", @image_link)
  else
      @content = @content.gsub("${image_link}", "No Uploaded Supplier Art")
  end

  @content
  end  
  
end
