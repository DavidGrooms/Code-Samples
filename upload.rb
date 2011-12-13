require 'RMagick'

class Upload < ActiveRecord::Base
 
  belongs_to :commitments 
  
  has_attachment  :storage => :file_system, 
                  :path_prefix => "/public/images/flyers",
                  :content_type => ['application/pdf', :image],
                  :partition => false,
                  :max_size => 3.megabytes,
                  :thumbnails => { :thumb => '125x160>' },
                  :processor => 'Rmagick' # attachment_fu looks in this order: ImageScience, Rmagick, MiniMagick


  validates_format_of :content_type, :with => /^image\//, :message => "Must be an Image file  .jpg"
  validates_as_attachment 
  
  def uploaded_data=(file_data)
    super
    self.filename = "#{File.extname(file_data.original_filename)}" if respond_to?(:filename)
  end
  
  def self.search(search)
    search_condition = "%" + search + "%"
    find(:all, :conditions => ['comid LIKE ?', search_condition])
  end
  
end
